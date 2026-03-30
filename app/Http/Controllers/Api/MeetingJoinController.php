<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\MeetingParticipant;
use App\Services\JitsiJwtService;
use App\Services\MeetingAccessPolicyService;
use App\Services\MeetingLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MeetingJoinController extends Controller
{
    public function __construct(
        private readonly JitsiJwtService $jitsiService,
        private readonly MeetingAccessPolicyService $accessPolicyService,
        private readonly MeetingLifecycleService $lifecycleService
    ) {}

    public function join(Request $request, Meeting $meeting): JsonResponse
    {
        $user = $request->user();
        $clientIp = $request->ip();

        if (!$meeting->isIpAllowed($clientIp)) {
            return response()->json([
                'message' => 'Access denied: Your IP address is not allowed to join this meeting.',
                'error_code' => 'ERR_IP_NOT_ALLOWED',
                'can_join' => false,
            ], 403);
        }

        if ($meeting->max_participants) {
            $recentJoinEvents = $meeting->events()
                ->where('type', 'participant_joined')
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->get();

            $uniqueParticipants = $recentJoinEvents->unique(function ($event) {
                $payload = $event->payload;
                return $payload['user_id'] ?? (($payload['user_name'] ?? 'guest') . '_' . ($payload['ip_address'] ?? 'n/a'));
            });

            $currentParticipantCount = $uniqueParticipants->count();
            if ($currentParticipantCount >= $meeting->max_participants) {
                return response()->json([
                    'message' => 'Meeting is full. Maximum participants limit reached.',
                    'error_code' => 'ERR_MEETING_FULL',
                    'can_join' => false,
                    'max_participants' => $meeting->max_participants,
                    'current_participants' => $currentParticipantCount,
                ], 403);
            }
        }

        if (!empty($meeting->password)) {
            $providedPassword = $request->input('password');
            if (!$meeting->verifyPassword($providedPassword)) {
                return response()->json([
                    'message' => 'Invalid meeting password.',
                    'error_code' => 'ERR_INVALID_PASSWORD',
                    'can_join' => false,
                    'requires_password' => true,
                ], 403);
            }
        }

        $policy = $this->accessPolicyService->evaluateJoin($request, $meeting, $user);
        if (!($policy['ok'] ?? false)) {
            return response()->json([
                'message' => $policy['message'],
                'error_code' => $policy['error_code'],
                'can_join' => false,
            ], 403);
        }

        $isModerator = $user && (
            $meeting->created_by === $user->id ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'host')->exists() ||
            $meeting->participants()->where('user_id', $user->id)->where('role', 'cohost')->exists()
        );

        $displayName = $user?->name
            ?? $request->input('display_name')
            ?? ($request->hasSession() ? $request->session()->get('guest_name', 'Guest') : 'Guest');

        $jwt = $this->jitsiService->generateToken(
            $meeting,
            $user,
            $displayName,
            $isModerator
        );

        if ($meeting->organization_id &&
            $meeting->organization &&
            $meeting->organization->require_jwt &&
            empty($jwt)) {
            return response()->json([
                'message' => 'JWT authentication is required but not properly configured.',
                'error_code' => 'ERR_JWT_REQUIRED_NOT_CONFIGURED',
                'can_join' => false,
            ], 500);
        }

        $this->lifecycleService->participantJoined($meeting, [
            'user_id' => $user?->id,
            'user_name' => $displayName,
            'is_moderator' => $isModerator,
            'ip_address' => $clientIp,
            'source' => 'app_join',
        ], true);

        if ($user) {
            $meeting->participants()->where('user_id', $user->id)->update(['joined_at' => Carbon::now()]);
        }

        return response()->json([
            'can_join' => true,
            'room_name' => $meeting->room_name,
            'domain' => config('services.jitsi.domain'),
            'jwt' => $jwt,
            'display_name' => $displayName,
            'avatar_url' => $user?->getJitsiAvatarUrl() ?? '',
            'is_moderator' => $isModerator,
            'config' => [
                'roomName' => $meeting->room_name,
                'width' => '100%',
                'height' => 600,
                'parentNode' => null,
                'userInfo' => [
                    'displayName' => $displayName,
                    'email' => $user?->email ?? (session('guest_email', '')),
                    'avatarURL' => $user?->getJitsiAvatarUrl() ?? '',
                ],
                'configOverwrite' => [
                    'prejoinPageEnabled' => false,
                    'prejoinConfig' => [
                        'enabled' => false,
                        'hideDisplayName' => true,
                    ],
                ],
            ],
        ]);
    }

    public function health(Meeting $meeting): JsonResponse
    {
        $domain = config('services.jitsi.domain');

        if (empty($domain)) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_HEALTH_DOMAIN_MISSING',
                'message' => 'Meeting domain is not configured.',
            ], 500);
        }

        try {
            $resp = Http::timeout(4)->get("https://{$domain}/config.js");
            if (!$resp->successful()) {
                return response()->json([
                    'ok' => false,
                    'error_code' => 'ERR_HEALTH_UNREACHABLE',
                    'message' => 'Meeting service is unreachable.',
                ], 503);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_HEALTH_UNREACHABLE',
                'message' => 'Meeting service is unreachable.',
            ], 503);
        }

        return response()->json([
            'ok' => true,
            'domain' => $domain,
            'meeting_id' => $meeting->id,
        ]);
    }

    public function pendingAdmissions(Request $request, Meeting $meeting): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$this->isModerator($meeting, $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $items = $meeting->participants()
            ->where('invite_status', 'invited')
            ->whereNull('user_id')
            ->orderByDesc('updated_at')
            ->get(['id', 'display_name', 'email', 'updated_at']);

        return response()->json(['items' => $items]);
    }

    public function decideAdmission(Request $request, Meeting $meeting, MeetingParticipant $participant): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$this->isModerator($meeting, $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ((string) $participant->meeting_id !== (string) $meeting->id) {
            return response()->json(['message' => 'Invalid participant'], 422);
        }

        $data = $request->validate([
            'action' => 'required|in:admit,reject',
        ]);

        $status = $data['action'] === 'admit' ? 'accepted' : 'declined';
        $participant->update(['invite_status' => $status]);

        MeetingEvent::create([
            'meeting_id' => $meeting->id,
            'type' => $status === 'accepted' ? 'admitted' : 'rejected',
            'payload' => [
                'participant_id' => $participant->id,
                'display_name' => $participant->display_name,
                'handled_by_user_id' => $user->id,
            ],
        ]);

        return response()->json(['success' => true, 'status' => $status]);
    }

    /**
     * Handle participant leaving the meeting
     */
    public function leave(Request $request, Meeting $meeting): JsonResponse
    {
        $user = $request->user();

        $this->lifecycleService->participantLeft($meeting, [
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $request->input('display_name'),
            'left_at' => Carbon::now()->toIso8601String(),
            'source' => 'app_leave',
        ], true);

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

    private function isModerator(Meeting $meeting, int $userId): bool
    {
        return (int) $meeting->created_by === $userId
            || $meeting->participants()->where('user_id', $userId)->whereIn('role', ['host', 'cohost'])->exists();
    }
}
