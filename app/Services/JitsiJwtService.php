<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\User;
use Firebase\JWT\JWT;

class JitsiJwtService
{
    public function generateToken(Meeting $meeting, ?User $user = null, ?string $guestName = null, bool $isModerator = false): ?string
    {
        // Check if JWT is required
        if (!$this->isJwtRequired($meeting)) {
            return null;
        }

        // Get expiry from organization or default
        $expiryMinutes = 120;
        if ($meeting->organization_id && $meeting->organization) {
            $expiryMinutes = $meeting->organization->jwt_expiry_minutes ?? 120;
        }

        $now = time();
        $exp = $now + ($expiryMinutes * 60);

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

    public function isJwtRequired(Meeting $meeting): bool
    {
        // If no secret configured, JWT is not possible
        if (empty(config('services.jitsi.secret'))) {
            return false;
        }

        // Check organization policy
        if ($meeting->organization_id && $meeting->organization) {
            return $meeting->organization->require_jwt;
        }

        // For personal meetings, JWT is optional but available
        return true;
    }
}
