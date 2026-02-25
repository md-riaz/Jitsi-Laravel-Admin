<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Services\JitsiJwtService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingJoinController extends Controller
{
    public function __construct(
        private readonly JitsiJwtService $jitsiService
    ) {}

    public function join(Request $request, Meeting $meeting): JsonResponse
    {
        // Check IP restriction
        $clientIp = $request->ip();
        if (!$meeting->isIpAllowed($clientIp)) {
            return response()->json([
                'message' => 'Access denied: Your IP address is not allowed to join this meeting.',
                'can_join' => false,
            ], 403);
        }

        // Check participant limit
        if ($meeting->max_participants) {
            // Count unique participants who joined in the last 24 hours from events
            // This is more reliable than meeting_participants table since join flow always creates events
            $recentJoinEvents = $meeting->events()
                ->where('type', 'participant_joined')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->get();

            // Count unique participants (by user_id for authenticated, by combination for guests)
            $uniqueParticipants = $recentJoinEvents->unique(function ($event) {
                $payload = $event->payload;
                return $payload['user_id'] ?? ($payload['user_name'] . '_' . $payload['ip_address']);
            });

            $currentParticipantCount = $uniqueParticipants->count();

            if ($currentParticipantCount >= $meeting->max_participants) {
                return response()->json([
                    'message' => 'Meeting is full. Maximum participants limit reached.',
                    'can_join' => false,
                    'max_participants' => $meeting->max_participants,
                    'current_participants' => $currentParticipantCount,
                ], 403);
            }
        }

        // Check password protection
        if (!empty($meeting->password)) {
            $providedPassword = $request->input('password');
            if (!$meeting->verifyPassword($providedPassword)) {
                return response()->json([
                    'message' => 'Invalid meeting password.',
                    'can_join' => false,
                    'requires_password' => true,
                ], 403);
            }
        }

        // Check guest policy
        $user = $request->user();
        $isGuest = !$user;
        if ($isGuest && !$meeting->allow_guests) {
            return response()->json([
                'message' => 'Guest access is not allowed for this meeting.',
                'can_join' => false,
            ], 403);
        }

        // Check if user can join at this time
        if (!$meeting->canJoinAt(Carbon::now())) {
            return response()->json([
                'message' => 'Meeting is not available for joining at this time.',
                'can_join' => false,
                'opens_at' => $meeting->start_at?->copy()->subMinutes($meeting->join_early_minutes),
                'closes_at' => $meeting->end_at?->copy()->addMinutes($meeting->join_late_minutes),
            ], 403);
        }

        // Determine if user is moderator
        $isModerator = $user && (
            $meeting->created_by === $user->id ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'host')->exists() ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'cohost')->exists()
        );

        // Generate JWT token if configured and required
        $jwt = $this->jitsiService->generateToken(
            $meeting,
            $user,
            $request->input('display_name'),
            $isModerator
        );

        // If organization requires JWT but we couldn't generate one, deny access
        if ($meeting->organization_id &&
            $meeting->organization &&
            $meeting->organization->require_jwt &&
            empty($jwt)) {
            return response()->json([
                'message' => 'JWT authentication is required but not properly configured.',
                'can_join' => false,
            ], 500);
        }

        // Log join event
        MeetingEvent::create([
            'meeting_id' => $meeting->id,
            'type' => 'participant_joined',
            'payload' => [
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $request->input('display_name'),
                'is_moderator' => $isModerator,
                'ip_address' => $clientIp,
            ],
        ]);

        // Update participant joined_at timestamp
        if ($user) {
            $meeting->participants()
                ->where('user_id', $user->id)
                ->update(['joined_at' => Carbon::now()]);
        }

        return response()->json([
            'can_join' => true,
            'room_name' => $meeting->room_name,
            'domain' => config('services.jitsi.domain'),
            'jwt' => $jwt,
            'display_name' => $user?->name ?? $request->input('display_name'),
            'avatar_url' => $user?->getJitsiAvatarUrl() ?? '',
            'is_moderator' => $isModerator,
            'config' => [
                'roomName' => $meeting->room_name,
                'width' => '100%',
                'height' => 600,
                'parentNode' => null,
                'userInfo' => [
                    'displayName' => $user?->name ?? $request->input('display_name'),
                    'email' => $user?->email ?? '',
                    'avatarURL' => $user?->getJitsiAvatarUrl() ?? '',
                ],
                'configOverwrite' => [
                    'prejoinPageEnabled' => $meeting->lobby_enabled,
                ],
            ],
        ]);
    }

    /**
     * Handle participant leaving the meeting
     */
    public function leave(Request $request, Meeting $meeting): JsonResponse
    {
        $user = $request->user();

        // Log leave event
        MeetingEvent::create([
            'meeting_id' => $meeting->id,
            'type' => 'participant_left',
            'payload' => [
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $request->input('display_name'),
                'left_at' => Carbon::now()->toIso8601String(),
            ],
        ]);

        // Update participant left_at timestamp
        if ($user) {
            $meeting->participants()
                ->where('user_id', $user->id)
                ->update(['left_at' => Carbon::now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Left meeting successfully',
        ]);
    }
}
