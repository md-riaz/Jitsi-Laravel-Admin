<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ((string) $meeting->created_by === (string) $user->id) {
            return true;
        }

        if ($meeting->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $this->sharesOrganization($user, $meeting);
    }

    public function manage(User $user, Meeting $meeting): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ((string) $meeting->created_by === (string) $user->id) {
            return true;
        }

        if ($meeting->participants()->where('user_id', $user->id)->whereIn('role', ['host', 'cohost'])->exists()) {
            return true;
        }

        return $this->sharesOrganization($user, $meeting)
            && method_exists($user, 'hasRole')
            && $user->hasRole('org-admin');
    }

    private function isSuperAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('super-admin');
    }

    private function sharesOrganization(User $user, Meeting $meeting): bool
    {
        return !empty($meeting->organization_id)
            && !empty($user->organization_id)
            && (string) $meeting->organization_id === (string) $user->organization_id;
    }
}
