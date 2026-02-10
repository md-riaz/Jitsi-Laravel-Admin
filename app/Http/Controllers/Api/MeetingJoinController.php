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
        // Check if user can join at this time
        if (! $meeting->canJoinAt(Carbon::now())) {
            return response()->json([
                'message' => 'Meeting is not available for joining at this time.',
                'can_join' => false,
                'opens_at' => $meeting->start_at->subMinutes($meeting->join_early_minutes),
                'closes_at' => $meeting->end_at->addMinutes($meeting->join_late_minutes),
            ], 403);
        }

        // Determine if user is moderator
        $user = $request->user();
        $isModerator = $user && (
            $meeting->created_by === $user->id ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'host')->exists() ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'cohost')->exists()
        );

        // Generate JWT token if secret is configured
        $jwt = null;
        if (config('services.jitsi.secret')) {
            $jwt = $this->jitsiService->generateToken(
                $meeting,
                $user,
                $request->input('display_name'),
                $isModerator
            );
        }

        // Log join event
        MeetingEvent::create([
            'meeting_id' => $meeting->id,
            'type' => 'participant_joined',
            'payload' => [
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $request->input('display_name'),
                'is_moderator' => $isModerator,
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
            'is_moderator' => $isModerator,
            'config' => [
                'roomName' => $meeting->room_name,
                'width' => '100%',
                'height' => 600,
                'parentNode' => null, // Will be set by frontend
                'userInfo' => [
                    'displayName' => $user?->name ?? $request->input('display_name'),
                    'email' => $user?->email ?? '',
                ],
            ],
        ]);
    }
}
