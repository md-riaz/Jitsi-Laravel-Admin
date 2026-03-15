<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingUsersController extends Controller
{
    /**
     * Ensure the current user is an org-admin with a valid organization.
     */
    private function authorizeOrgAdmin(): User
    {
        $user = Auth::user();

        if (!$user->isOrganizationUser() || !$user->organization_id) {
            abort(403, 'Only organization admins can manage pending registrations.');
        }

        if (!$user->hasRole('org-admin')) {
            abort(403, 'Only organization admins can manage pending registrations.');
        }

        return $user;
    }

    /**
     * List pending users for the current org admin's organization.
     */
    public function index()
    {
        $admin = $this->authorizeOrgAdmin();

        $pendingUsers = User::where('organization_id', $admin->organization_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $organization = $admin->organization;

        return view('dashboard.pending-users', compact('pendingUsers', 'organization'));
    }

    /**
     * Approve a pending user registration.
     */
    public function approve($id)
    {
        $admin = $this->authorizeOrgAdmin();

        $user = User::where('id', $id)
            ->where('organization_id', $admin->organization_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $user->status = 'active';
        $user->save();

        return redirect()->route('dashboard.pending-users.index')
            ->with('success', $user->name . ' has been approved and can now log in.');
    }

    /**
     * Reject (remove) a pending user registration.
     */
    public function reject($id)
    {
        $admin = $this->authorizeOrgAdmin();

        $user = User::where('id', $id)
            ->where('organization_id', $admin->organization_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $name = $user->name;

        // Remove from organization pivot
        $admin->organization->users()->detach($user->id);

        // Delete the user record entirely
        $user->delete();

        return redirect()->route('dashboard.pending-users.index')
            ->with('success', $name . '\'s registration has been rejected and removed.');
    }
}
