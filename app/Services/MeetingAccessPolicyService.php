<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeetingAccessPolicyService
{
    public function __construct(
        private readonly MeetingInviteService $inviteService
    ) {}

    public function evaluateJoin(Request $request, Meeting $meeting, ?User $user): array
    {
        $visibility = Meeting::normalizeVisibility($meeting->visibility);

        // Org-only strict membership gate (except creator/super-admin)
        if ($visibility === 'org_only') {
            if (!$user) {
                return $this->deny('ERR_ORG_MEMBERS_ONLY', 'This meeting is restricted to organization members.');
            }

            $isOwner = (int) $meeting->created_by === (int) $user->id;
            $isSuperAdmin = method_exists($user, 'hasRole') ? $user->hasRole('super-admin') : false;
            $sameOrg = $meeting->organization_id && $user->organization_id
                ? (string) $meeting->organization_id === (string) $user->organization_id
                : false;

            if (!$isOwner && !$isSuperAdmin && !$sameOrg) {
                return $this->deny('ERR_ORG_MEMBERS_ONLY', 'Only members of the meeting organization can join.');
            }
        }

        $inviteValidated = false;

        // Invite-only guest enforcement
        if (!$user && $visibility === 'invite_only') {
            $inviteToken = (string) ($request->input('invite_token') ?: ($request->hasSession() ? $request->session()->get('invite_token', '') : ''));
            if ($inviteToken === '') {
                return $this->deny('ERR_INVITE_REQUIRED', 'This meeting requires a valid invitation.');
            }

            $invite = $this->inviteService->validateInvite($inviteToken);
            if (!$invite) {
                return $this->deny('ERR_INVITE_EXPIRED', 'Invitation is invalid or expired.');
            }

            if ((string) $invite->meeting_id !== (string) $meeting->id) {
                return $this->deny('ERR_INVITE_REQUIRED', 'Invitation does not match this meeting.');
            }

            $inviteValidated = true;
        }

        // Guest gate from computed policy (invite_only with valid invite is allowed)
        if (!$user && !$meeting->allow_guests && !($visibility === 'invite_only' && $inviteValidated)) {
            return $this->deny('ERR_GUEST_NOT_ALLOWED', 'Guest access is not allowed for this meeting.');
        }

        // Time window gate
        if (!$meeting->canJoinAt(Carbon::now())) {
            return $this->deny('ERR_OUTSIDE_JOIN_WINDOW', 'Meeting is not available for joining at this time.');
        }

        return ['ok' => true];
    }

    private function deny(string $code, string $message): array
    {
        return [
            'ok' => false,
            'error_code' => $code,
            'message' => $message,
        ];
    }
}
