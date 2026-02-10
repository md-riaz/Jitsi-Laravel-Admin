<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Firebase\JWT\JWT;

class JitsiJwtService
{
    public function generateToken(Meeting $meeting, ?User $user = null, ?string $guestName = null, bool $isModerator = false): string
    {
        $now = time();
        $exp = $now + (2 * 60 * 60); // 2 hours

        $payload = [
            'aud' => config('services.jitsi.audience'),
            'iss' => config('services.jitsi.issuer'),
            'sub' => config('services.jitsi.sub'),
            'room' => $meeting->room_name,
            'exp' => $exp,
            'nbf' => $now,
            'iat' => $now,
            'context' => [
                'user' => [
                    'name' => $user?->name ?? $guestName ?? 'Guest',
                    'email' => $user?->email ?? '',
                    'moderator' => $isModerator,
                ],
            ],
        ];

        $secret = config('services.jitsi.secret');

        return JWT::encode($payload, $secret, 'HS256');
    }
}
