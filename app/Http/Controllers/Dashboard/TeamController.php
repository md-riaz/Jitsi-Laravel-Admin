<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Ensure the current user is an org-admin with a valid organization.
     */
    private function authorizeOrgAdmin(): User
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
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

        if ($member->organization_id !== $admin->organization_id) {
            abort(403, 'You can only manage members of your organization.');
        }

        return $member;
    }

    /**
     * Display a listing of team members
     */
    public function index(Request $request)
    {
        $admin = $this->authorizeOrgAdmin();
        $organization = $admin->organization;

        $query = $organization->users()->withPivot('role', 'created_at');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->input('role')) {
            $query->wherePivot('role', $role);
        }

        $teamMembers = $query->get();

        return view('dashboard.team.index', compact('organization', 'teamMembers'));
    }

    /**
     * Show the form for inviting a new team member
     */
    public function create()
    {
        $this->authorizeOrgAdmin();

        return view('dashboard.team.create');
    }

    /**
     * Invite a new team member
     */
    public function store(Request $request)
    {
        $admin = $this->authorizeOrgAdmin();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,host,member',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

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

        $teamMember->name = $data['name'];
        $teamMember->email = $data['email'];
        if (!empty($data['password'])) {
            $teamMember->password = Hash::make($data['password']);
        }
        $teamMember->save();

        // Update role in pivot table
        $admin->organization->users()->updateExistingPivot($teamMember->id, ['role' => $data['role']]);

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

        $admin->organization->users()->detach($teamMember->id);

        $teamMember->organization_id = null;
        $teamMember->account_type = 'single';
        $teamMember->save();

        $teamMember->removeRole('org-admin');
        $teamMember->removeRole('member');
        if (!$teamMember->hasRole('host')) {
            $teamMember->assignRole('host');
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member removed from organization successfully!');
    }

    /**
     * Assign a system role to a user based on an org-level role string.
     */
    private function assignOrgRole(User $user, string $role): void
    {
        if ($role === 'admin') {
            $user->assignRole('org-admin');
        } elseif ($role === 'host') {
            $user->assignRole('host');
        } else {
            $user->assignRole('member');
        }
    }

    /**
     * Sync a user's system roles to match their org-level role.
     */
    private function syncOrgRole(User $user, string $role): void
    {
        if ($role === 'admin') {
            $user->removeRole('host');
            $user->removeRole('member');
            $user->assignRole('org-admin');
        } elseif ($role === 'host') {
            $user->removeRole('org-admin');
            $user->removeRole('member');
            $user->assignRole('host');
        } else {
            $user->removeRole('org-admin');
            $user->removeRole('host');
            $user->assignRole('member');
        }
    }
}
