<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\MeetingJoinController;
use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\MeetingParticipant;
use App\Services\MeetingAccessPolicyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct(
        private readonly MeetingJoinController $joinController,
        private readonly MeetingAccessPolicyService $accessPolicy
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = Meeting::query()
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('participants', fn($p) => $p->where('user_id', $user->id));
                if ($user->organization_id) {
                    $q->orWhere('organization_id', $user->organization_id);
                }
            })
            ->with(['organization:id,name'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'ok' => true,
            'data' => $items,
        ]);
    }

    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $meeting->load(['organization:id,name,require_jwt,jwt_expiry_minutes']);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'description' => $meeting->description,
                'visibility' => Meeting::normalizeVisibility($meeting->visibility),
                'allow_guests' => (bool) $meeting->allow_guests,
                'lobby_enabled' => (bool) $meeting->lobby_enabled,
                'status' => $meeting->status,
                'start_at' => $meeting->start_at,
                'end_at' => $meeting->end_at,
                'timezone' => $meeting->timezone,
                'organization' => $meeting->organization,
            ],
        ]);
    }

    public function health(Request $request, Meeting $meeting): JsonResponse
    {
        $resp = $this->joinController->health($meeting);
        $payload = $resp->getData(true);
        return response()->json([
            'ok' => $payload['ok'] ?? false,
            'data' => $payload['ok'] ? $payload : null,
            'error_code' => $payload['error_code'] ?? null,
            'message' => $payload['message'] ?? null,
        ], $resp->getStatusCode());
    }

    public function join(Request $request, Meeting $meeting): JsonResponse
    {
        $resp = $this->joinController->join($request, $meeting);
        $payload = $resp->getData(true);

        if (($payload['can_join'] ?? false) === true) {
            return response()->json([
                'ok' => true,
                'data' => [
                    'can_join' => true,
                    'meeting' => [
                        'id' => $meeting->id,
                        'title' => $meeting->title,
                        'visibility' => Meeting::normalizeVisibility($meeting->visibility),
                        'allow_guests' => (bool) $meeting->allow_guests,
                        'lobby_enabled' => (bool) $meeting->lobby_enabled,
                    ],
                    'jitsi' => [
                        'domain' => $payload['domain'] ?? config('services.jitsi.domain'),
                        'room_name' => $payload['room_name'] ?? $meeting->room_name,
                        'jwt' => $payload['jwt'] ?? null,
                        'display_name' => $payload['display_name'] ?? null,
                        'avatar_url' => $payload['avatar_url'] ?? null,
                        'is_moderator' => $payload['is_moderator'] ?? false,
                    ],
                ],
            ], $resp->getStatusCode());
        }

        return response()->json([
            'ok' => false,
            'error_code' => $payload['error_code'] ?? 'ERR_JOIN_DENIED',
            'message' => $payload['message'] ?? 'Unable to join meeting',
            'details' => $payload,
        ], $resp->getStatusCode());
    }

    public function leave(Request $request, Meeting $meeting): JsonResponse
    {
        $resp = $this->joinController->leave($request, $meeting);
        $payload = $resp->getData(true);

        return response()->json([
            'ok' => true,
            'data' => $payload,
        ], $resp->getStatusCode());
    }

    public function pendingAdmissions(Request $request, Meeting $meeting): JsonResponse
    {
        $resp = $this->joinController->pendingAdmissions($request, $meeting);
        $payload = $resp->getData(true);
        return response()->json([
            'ok' => $resp->getStatusCode() < 300,
            'data' => $payload,
            'error_code' => $resp->getStatusCode() >= 300 ? 'ERR_FORBIDDEN' : null,
        ], $resp->getStatusCode());
    }

    public function decideAdmission(Request $request, Meeting $meeting, MeetingParticipant $participant): JsonResponse
    {
        $resp = $this->joinController->decideAdmission($request, $meeting, $participant);
        $payload = $resp->getData(true);
        return response()->json([
            'ok' => $resp->getStatusCode() < 300,
            'data' => $payload,
            'error_code' => $resp->getStatusCode() >= 300 ? 'ERR_FORBIDDEN' : null,
        ], $resp->getStatusCode());
    }

    public function admissionStatus(Request $request, Meeting $meeting): JsonResponse
    {
        $data = $request->validate([
            'participant_id' => 'required|string',
        ]);

        $participant = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('id', $data['participant_id'])
            ->first();

        if (!$participant) {
            return response()->json([
                'ok' => false,
                'error_code' => 'ERR_PARTICIPANT_NOT_FOUND',
                'message' => 'Participant not found',
            ], 404);
        }

        $statusMap = [
            'invited' => 'pending',
            'accepted' => 'admitted',
            'declined' => 'rejected',
            'bounced' => 'rejected',
        ];

        return response()->json([
            'ok' => true,
            'data' => [
                'participant_id' => $participant->id,
                'status' => $statusMap[$participant->invite_status] ?? $participant->invite_status,
            ],
        ]);
    }

    public function summary(Request $request, Meeting $meeting): JsonResponse
    {
        $events = MeetingEvent::where('meeting_id', $meeting->id)->orderBy('created_at')->get();
        $joinEvents = $events->where('type', 'participant_joined');
        $leaveEvents = $events->where('type', 'participant_left');
        $firstJoin = optional($joinEvents->first())->created_at;
        $lastLeave = optional($leaveEvents->last())->created_at ?? optional($events->last())->created_at;

        return response()->json([
            'ok' => true,
            'data' => [
                'meeting_id' => $meeting->id,
                'unique_participants' => MeetingParticipant::where('meeting_id', $meeting->id)->count(),
                'peak_participants' => $this->calculatePeakParticipants($events),
                'join_events' => $joinEvents->count(),
                'leave_events' => $leaveEvents->count(),
                'first_join' => $firstJoin,
                'last_leave' => $lastLeave,
                'duration_minutes' => ($firstJoin && $lastLeave)
                    ? Carbon::parse($firstJoin)->diffInMinutes(Carbon::parse($lastLeave))
                    : null,
            ],
        ]);
    }

    public function timeline(Request $request, Meeting $meeting): JsonResponse
    {
        $items = MeetingEvent::where('meeting_id', $meeting->id)
            ->orderBy('created_at')
            ->get(['id', 'type', 'payload', 'created_at']);

        return response()->json([
            'ok' => true,
            'data' => $items,
        ]);
    }

    public function attendance(Request $request, Meeting $meeting): JsonResponse
    {
        $rows = MeetingParticipant::where('meeting_id', $meeting->id)
            ->get(['id', 'display_name', 'email', 'user_id', 'joined_at', 'left_at'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->display_name,
                    'identity' => $p->email ?: ('user:' . ($p->user_id ?? 'n/a')),
                    'joined_at' => $p->joined_at,
                    'left_at' => $p->left_at,
                    'duration_minutes' => ($p->joined_at && $p->left_at)
                        ? Carbon::parse($p->joined_at)->diffInMinutes(Carbon::parse($p->left_at))
                        : null,
                ];
            });

        return response()->json([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    public function diagnostics(Request $request, Meeting $meeting): JsonResponse
    {
        $visibility = Meeting::normalizeVisibility($meeting->visibility);
        $user = $request->user();
        $policy = $this->accessPolicy->evaluateJoin($request, $meeting, $user);

        return response()->json([
            'ok' => true,
            'data' => [
                'meeting_id' => $meeting->id,
                'visibility' => $visibility,
                'allow_guests' => (bool) $meeting->allow_guests,
                'lobby_enabled' => (bool) $meeting->lobby_enabled,
                'jwt' => [
                    'domain' => config('services.jitsi.domain'),
                    'issuer' => config('services.jitsi.issuer'),
                    'audience' => config('services.jitsi.audience'),
                    'sub' => config('services.jitsi.sub'),
                    'has_secret' => !empty(config('services.jitsi.secret')),
                ],
                'policy_for_current_user' => $policy,
                'recent_denials' => MeetingEvent::where('meeting_id', $meeting->id)
                    ->whereIn('type', ['join_denied', 'rejected', 'admission_requested'])
                    ->latest()->limit(20)->get(['id', 'type', 'payload', 'created_at']),
            ],
        ]);
    }

    private function calculatePeakParticipants($events): int
    {
        $count = 0;
        $peak = 0;
        foreach ($events as $e) {
            if ($e->type === 'participant_joined') {
                $count++;
                $peak = max($peak, $count);
            } elseif ($e->type === 'participant_left') {
                $count = max(0, $count - 1);
            }
        }
        return $peak;
    }
}
