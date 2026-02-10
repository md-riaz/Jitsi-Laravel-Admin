<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingInvite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MeetingInviteService
{
    public function createInvite(Meeting $meeting, string $email): array
    {
        $plainToken = Str::random(64);

        $invite = MeetingInvite::create([
            'meeting_id' => $meeting->id,
            'email' => $email,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => $meeting->end_at->addDay(),
        ]);

        return [
            'invite' => $invite,
            'token' => $plainToken,
        ];
    }

    public function validateInvite(string $token): ?MeetingInvite
    {
        $invites = MeetingInvite::where('revoked_at', null)
            ->where('expires_at', '>', Carbon::now())
            ->get();

        foreach ($invites as $invite) {
            if (Hash::check($token, $invite->token_hash)) {
                return $invite;
            }
        }

        return null;
    }

    public function revokeInvite(MeetingInvite $invite): void
    {
        $invite->update(['revoked_at' => Carbon::now()]);
    }
}
