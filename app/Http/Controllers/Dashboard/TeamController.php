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
     * Display a listing of team members
     */
    public function index()
    {
        $user = Auth::user();

        // Only organization users can access team management
        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        $organization = $user->organization;
        $teamMembers = $organization->users()->withPivot('role', 'created_at')->get();

        return view('dashboard.team.index', compact('organization', 'teamMembers'));
    }

    /**
     * Show the form for inviting a new team member
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        return view('dashboard.team.create');
    }

    /**
     * Invite a new team member
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Create the new team member
        $newMember = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'account_type' => 'organization',
            'organization_id' => $user->organization_id,
        ]);

        // Assign appropriate role
        if ($data['role'] === 'admin') {
            $newMember->assignRole('org-admin');
        } else {
            $newMember->assignRole('host');
        }

        // Add to organization pivot table
        $user->organization->users()->attach($newMember->id, ['role' => $data['role']]);

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member invited successfully!');
    }

    /**
     * Show the form for editing a team member's role
     */
    public function edit($id)
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        $teamMember = User::findOrFail($id);

        // Ensure the team member belongs to the same organization
        if ($teamMember->organization_id !== $user->organization_id) {
            abort(403, 'You can only edit members of your organization.');
        }

        return view('dashboard.team.edit', compact('teamMember'));
    }

    /**
     * Update a team member's role
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $teamMember = User::findOrFail($id);

        // Ensure the team member belongs to the same organization
        if ($teamMember->organization_id !== $user->organization_id) {
            abort(403, 'You can only edit members of your organization.');
        }

        // Prevent users from changing their own role
        if ($teamMember->id === $user->id) {
            return redirect()->back()
                ->with('error', 'You cannot change your own role.');
        }

        $data = $validator->validated();

        // Update role in pivot table
        $user->organization->users()->updateExistingPivot($teamMember->id, ['role' => $data['role']]);

        // Update user's role in the system
        if ($data['role'] === 'admin') {
            $teamMember->removeRole('host');
            $teamMember->assignRole('org-admin');
        } else {
            $teamMember->removeRole('org-admin');
            $teamMember->assignRole('host');
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member role updated successfully!');
    }

    /**
     * Remove a team member from the organization
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage teams.');
        }

        $teamMember = User::findOrFail($id);

        // Ensure the team member belongs to the same organization
        if ($teamMember->organization_id !== $user->organization_id) {
            abort(403, 'You can only remove members of your organization.');
        }

        // Prevent users from removing themselves
        if ($teamMember->id === $user->id) {
            return redirect()->back()
                ->with('error', 'You cannot remove yourself from the team.');
        }

        // Remove from organization pivot table
        $user->organization->users()->detach($teamMember->id);

        // Update user's organization_id to null and change to single account
        $teamMember->organization_id = null;
        $teamMember->account_type = 'single';
        $teamMember->save();

        // Update roles
        $teamMember->removeRole('org-admin');
        if (!$teamMember->hasRole('host')) {
            $teamMember->assignRole('host');
        }

        return redirect()->route('dashboard.team.index')
            ->with('success', 'Team member removed from organization successfully!');
    }
}
