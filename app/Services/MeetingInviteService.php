<?php

namespace App\Services;

use App\Mail\MeetingInvitationMail;
use App\Models\Meeting;
use App\Models\MeetingInvite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MeetingInviteService
{
    public function createInvite(Meeting $meeting, string $email, bool $sendEmail = true): array
    {
        $plainToken = Str::random(64);

        $invite = MeetingInvite::create([
            'meeting_id' => $meeting->id,
            'email' => $email,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => $meeting->end_at ? $meeting->end_at->addDay() : Carbon::now()->addWeek(),
        ]);

        $result = [
            'invite' => $invite,
            'token' => $plainToken,
        ];

        // Send email invitation if requested
        if ($sendEmail) {
            try {
                Mail::to($email)->send(new MeetingInvitationMail($meeting, $invite, $plainToken));
                $result['email_sent'] = true;
            } catch (\Exception $e) {
                $result['email_sent'] = false;
                $result['email_error'] = $e->getMessage();
            }
        }

        return $result;
    }

    public function sendInvite(MeetingInvite $invite, string $plainToken): bool
    {
        try {
            Mail::to($invite->email)->send(
                new MeetingInvitationMail($invite->meeting, $invite, $plainToken)
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
