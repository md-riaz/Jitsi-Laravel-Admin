<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Ensure the current user is an org-admin with a valid organization.
     */
    private function authorizeOrgAdmin(): User
    {
        $user = Auth::user();

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return $user;
        }

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage team members.');
        }

        if (!method_exists($user, 'hasRole') || !$user->hasRole('org-admin')) {
            abort(403, 'Only organization admins can manage team members.');
        }

        return $user;
    }

    /**
     * Ensure the target member belongs to the admin's organization.
     */
    private function resolveOrgMember(User $admin, int|string $id): User
    {
        $member = User::findOrFail($id);

        if (method_exists($admin, 'hasRole') && $admin->hasRole('super-admin')) {
            return $member;
        }

        if ($member->organization_id !== $admin->organization_id) {
            abort(403, 'You can only manage members of your organization.');
        }

        return $member;
    }

    private function isOrganizationOwnerAccount(User $user): bool
    {
        return $user->organization
            && $user->organization->owner_id !== null
            && (int) $user->organization->owner_id === (int) $user->id;
    }

    private function isOrganizationOwner(User $user, Organization $organization): bool
    {
        return $organization->owner_id !== null && (int) $organization->owner_id === (int) $user->id;
    }

    private function denyTeamAction(string $message): RedirectResponse
    {
        return redirect()->back()->with('error', $message);
    }

    private function ensureAllowedTeamAction(User $admin, User $teamMember, string $actionLabel, ?string $targetRole = null): ?RedirectResponse
    {
        if ($this->isOrganizationOwnerAccount($teamMember)) {
            return $this->denyTeamAction('This account is the organization owner and cannot be ' . $actionLabel . '.');
        }

        if (method_exists($admin, 'hasRole') && $admin->hasRole('super-admin')) {
            return null;
        }

        $organization = $teamMember->organization;
        if (!$organization) {
            return $this->denyTeamAction('Unable to validate organization ownership boundary for this user.');
        }

        $adminIsOwner = $this->isOrganizationOwner($admin, $organization);
        $targetIsAdmin = method_exists($teamMember, 'hasRole') && $teamMember->hasRole('org-admin');

        if (!$adminIsOwner && $targetIsAdmin) {
            return $this->denyTeamAction('Only the organization owner can manage admin accounts.');
        }

        if ($targetRole === 'admin' && !$adminIsOwner) {
            return $this->denyTeamAction('Only the organization owner can promote users to admin.');
        }

        return null;
    }

    /**
     * Display a listing of team members
     */
    public function index(Request $request)
    {
        $admin = $this->authorizeOrgAdmin();
        $organization = $admin->organization;

        // Query by FK so users created via any path (super admin, org admin, self-registration) all appear.
        $query = User::where('organization_id', $admin->organization_id)
                     ->with(['roles', 'organization']);

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter — map display role to system role slug
        if ($role = $request->input('role')) {
            $roleSlug = $role === 'admin' ? 'org-admin' : $role;
            $query->whereHas('roles', fn ($q) => $q->where('slug', $roleSlug));
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $teamMembers = $query->orderBy('created_at', 'desc')->get();

        return view('dashboard.team.index', compact('organization', 'teamMembers'));
    }

    /**
     * Show the form for inviting a new team member
     */
    public function create()
    {
        $admin = $this->authorizeOrgAdmin();

        $organizations = collect();
        if (method_exists($admin, 'hasRole') && $admin->hasRole('super-admin')) {
            $organizations = Organization::orderBy('name')->get(['id', 'name']);
        }

        return view('dashboard.team.create', compact('organizations'));
    }

    /**
     * Invite a new team member
     */
    public function store(Request $request)
    {
        $admin = $this->authorizeOrgAdmin();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,host,member',
            'provisioning_mode' => 'nullable|in:new,existing',
            'organization_name' => 'nullable|string|max:255',
            'organization_id' => 'nullable|uuid|exists:organizations,id',
        ];

        if (method_exists($admin, 'hasRole') && $admin->hasRole('super-admin')) {
            $request->merge([
                'provisioning_mode' => $request->input('provisioning_mode', 'new'),
            ]);

            if ($request->input('provisioning_mode') === 'existing') {
                $rules['organization_id'] = 'required|uuid|exists:organizations,id';
            } else {
                $rules['organization_name'] = 'required|string|max:255';
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            'organization_name.required' => 'Please provide an organization name for the new organization.',
            'organization_id.required' => 'Please select an existing organization.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if (method_exists($admin, 'hasRole') && $admin->hasRole('super-admin')) {
            $mode = $data['provisioning_mode'] ?? 'new';

            if ($mode === 'new' && $data['role'] !== 'admin') {
                return redirect()->back()
                    ->withErrors(['role' => 'When creating a new organization, the first user must be an admin.'])
                    ->withInput();
            }

            if ($mode === 'new') {
                $organization = DB::transaction(function () use ($data) {
                    $organization = $this->createOrganization($data['organization_name'], null);

                    $newMember = User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'account_type' => 'organization',
                        'status' => 'active',
                        'organization_id' => $organization->id,
                    ]);

                    $this->assignOrgRole($newMember, 'admin');
                    $organization->users()->attach($newMember->id, ['role' => 'admin']);
                    $organization->assignOwnerIfMissing($newMember);

                    return $organization;
                });

                return redirect()->route('dashboard.team.create')
                    ->with('success', 'Organization and initial admin created successfully for ' . $organization->name . '.');
            }

            $organization = Organization::findOrFail($data['organization_id']);

            $newMember = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'account_type' => 'organization',
                'status' => 'active',
                'organization_id' => $organization->id,
            ]);

            $this->assignOrgRole($newMember, $data['role']);
            $organization->users()->attach($newMember->id, ['role' => $data['role']]);

            return redirect()->route('dashboard.team.create')
                ->with('success', 'User created successfully for ' . $organization->name . '.');
        }

        $newMember = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'account_type' => 'organization',
            'organization_id' => $admin->organization_id,
        ]);

        $this->assignOrgRole($newMember, $data['role']);

        $admin->organization->users()->attach($newMember->id, ['role' => $data['role']]);

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member added successfully!');
    }

    /**
     * Show the form for editing a team member
     */
    public function edit($id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        return view('dashboard.team.edit', compact('teamMember'));
    }

    /**
     * Update a team member's profile and role
     */
    public function update(Request $request, $id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        if ($teamMember->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot edit your own account here.');
        }

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'edited')) {
            return $denial;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teamMember->id,
            'role' => 'required|in:admin,host,member',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'role changed', $data['role'])) {
            return $denial;
        }

        $teamMember->name = $data['name'];
        $teamMember->email = $data['email'];
        if (!empty($data['password'])) {
            $teamMember->password = Hash::make($data['password']);
        }
        $teamMember->save();

        // Update role in pivot table
        $teamMember->organization?->users()->updateExistingPivot($teamMember->id, ['role' => $data['role']]);

        // Sync system roles
        $this->syncOrgRole($teamMember, $data['role']);

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member updated successfully!');
    }

    /**
     * Suspend a team member
     */
    public function suspend(Request $request, $id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        if ($teamMember->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot suspend yourself.');
        }

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'suspended')) {
            return $denial;
        }

        $reason = $request->input('reason', '');

        if (method_exists($teamMember, 'suspend')) {
            $teamMember->suspend($reason);
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', $teamMember->name . ' has been suspended.');
    }

    /**
     * Unsuspend a team member
     */
    public function unsuspend($id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'unsuspended')) {
            return $denial;
        }

        if (method_exists($teamMember, 'unsuspend')) {
            $teamMember->unsuspend();
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', $teamMember->name . ' has been unsuspended.');
    }

    /**
     * Log in as a team member (impersonation)
     */
    public function loginAs($id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        if ($teamMember->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'impersonated')) {
            return $denial;
        }

        // Store the admin's ID so the impersonation banner can display it
        session(['impersonator_id' => $admin->id]);

        Auth::login($teamMember);

        return redirect()->route('tyro-dashboard.index')
            ->with('success', 'Now logged in as ' . $teamMember->name . '.');
    }

    /**
     * Remove a team member from the organization
     */
    public function destroy($id)
    {
        $admin = $this->authorizeOrgAdmin();
        $teamMember = $this->resolveOrgMember($admin, $id);

        if ($teamMember->id === $admin->id) {
            return redirect()->back()->with('error', 'You cannot remove yourself from the team.');
        }

        if ($denial = $this->ensureAllowedTeamAction($admin, $teamMember, 'removed')) {
            return $denial;
        }

        $teamMember->organization?->users()->detach($teamMember->id);

        $teamMember->organization_id = null;
        $teamMember->account_type = 'single';
        $teamMember->save();

        $teamMember->removeRole(Role::where('slug', 'org-admin')->firstOrFail());
        $teamMember->removeRole(Role::where('slug', 'member')->firstOrFail());
        if (!$teamMember->hasRole('host')) {
            $teamMember->assignRole(Role::where('slug', 'host')->firstOrFail());
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member removed from organization successfully!');
    }

    private function createOrganization(string $name, ?int $ownerId): Organization
    {
        $name = trim($name);
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $suffix = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $baseSlug !== '' ? $baseSlug . '-' . $suffix : Str::lower(Str::random(8));
            $suffix++;
        }

        return Organization::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
            'owner_id' => $ownerId,
        ]);
    }

    /**
     * Assign a system role to a user based on an org-level role string.
     */
    private function assignOrgRole(User $user, string $role): void
    {
        if ($role === 'admin') {
            $roleModel = Role::where('slug', 'org-admin')->firstOrFail();
            $user->assignRole($roleModel);
        } elseif ($role === 'host') {
            $roleModel = Role::where('slug', 'host')->firstOrFail();
            $user->assignRole($roleModel);
        } else {
            $roleModel = Role::where('slug', 'member')->firstOrFail();
            $user->assignRole($roleModel);
        }
    }

    /**
     * Sync a user's system roles to match their org-level role.
     */
    private function syncOrgRole(User $user, string $role): void
    {
        if ($role === 'admin') {
            $user->removeRole(Role::where('slug', 'host')->firstOrFail());
            $user->removeRole(Role::where('slug', 'member')->firstOrFail());
            $user->assignRole(Role::where('slug', 'org-admin')->firstOrFail());
        } elseif ($role === 'host') {
            $user->removeRole(Role::where('slug', 'org-admin')->firstOrFail());
            $user->removeRole(Role::where('slug', 'member')->firstOrFail());
            $user->assignRole(Role::where('slug', 'host')->firstOrFail());
        } else {
            $user->removeRole(Role::where('slug', 'org-admin')->firstOrFail());
            $user->removeRole(Role::where('slug', 'host')->firstOrFail());
            $user->assignRole(Role::where('slug', 'member')->firstOrFail());
        }
    }
}
