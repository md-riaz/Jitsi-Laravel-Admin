<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function editTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'edit this account');
    }

    public function updateTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'update this account');
    }

    public function suspendTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'suspend this account');
    }

    public function unsuspendTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'unsuspend this account');
    }

    public function impersonateTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'impersonate this account');
    }

    public function removeTeamMember(User $admin, User $target): Response
    {
        return $this->teamActionCheck($admin, $target, 'remove this account');
    }

    private function teamActionCheck(User $admin, User $target, string $actionText): Response
    {
        if ($this->isSuperAdmin($admin)) {
            return Response::allow();
        }

        if (!method_exists($admin, 'hasRole') || !$admin->hasRole('org-admin')) {
            return Response::deny('Only organization admins can manage team members.');
        }

        if ((int) $admin->id === (int) $target->id) {
            return Response::deny('You cannot perform this action on your own account.');
        }

        if ((string) $admin->organization_id !== (string) $target->organization_id) {
            return Response::deny('You can only manage members of your organization.');
        }

        if ($this->isOrganizationOwner($target)) {
            return Response::deny('The organization owner account cannot be managed by another admin.');
        }

        if ($this->isOrgAdmin($target) && !$this->isOrganizationOwner($admin)) {
            return Response::deny('Only the organization owner can ' . $actionText . ' for another admin.');
        }

        return Response::allow();
    }

    private function isSuperAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('super-admin');
    }

    private function isOrgAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('org-admin');
    }

    private function isOrganizationOwner(User $user): bool
    {
        $organization = $user->organization;

        return $organization instanceof Organization
            && $organization->owner_id !== null
            && (int) $organization->owner_id === (int) $user->id;
    }
}
