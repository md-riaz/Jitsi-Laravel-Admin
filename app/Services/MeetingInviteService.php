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
            'token_lookup' => hash('sha256', $plainToken),
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
        $invite = MeetingInvite::where('token_lookup', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($invite && Hash::check($token, $invite->token_hash)) {
            return $invite;
        }

        // Fallback for older tokens without lookup hash (if N is small enough to tolerate)
        // or just rely on new tokens having the hash.
        // For v1 rollout, we'll assume new tokens are the priority.

        return null;
    }

    public function revokeInvite(MeetingInvite $invite): void
    {
        $invite->update(['revoked_at' => Carbon::now()]);
    }
}
