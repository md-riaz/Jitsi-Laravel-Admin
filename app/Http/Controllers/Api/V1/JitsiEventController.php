<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\MeetingLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JitsiEventController extends Controller
{
    public function __construct(
        private readonly MeetingLifecycleService $lifecycleService
    ) {}

    public function ingest(Request $request): JsonResponse
    {
        $secret = (string) config('services.jitsi.webhook_secret', '');
        $providedSecret = (string) ($request->header('X-Jitsi-Webhook-Secret') ?: $request->bearerToken() ?: '');

        if ($secret === '' || !hash_equals($secret, $providedSecret)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized webhook request.',
            ], 401);
        }

        $payload = $request->validate([
            'event' => 'required|string',
            'room_name' => 'nullable|string',
            'room' => 'nullable|string',
            'meeting_id' => 'nullable|string',
            'participant' => 'nullable|array',
            'data' => 'nullable|array',
            'ended_by' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $meeting = $this->resolveMeeting($payload);
        if (!$meeting) {
            return response()->json([
                'ok' => false,
                'message' => 'Meeting not found for webhook payload.',
            ], 404);
        }

        $event = strtolower((string) $payload['event']);
        $detail = array_filter([
            'participant' => $payload['participant'] ?? null,
            'data' => $payload['data'] ?? null,
            'ended_by' => $payload['ended_by'] ?? null,
            'reason' => $payload['reason'] ?? null,
            'source' => 'jitsi_webhook',
        ], fn ($value) => $value !== null);

        match ($event) {
            'participant_joined', 'occupant_joined', 'muc-occupant-joined' => $this->lifecycleService->participantJoined($meeting, $detail, true),
            'participant_left', 'occupant_left', 'muc-occupant-left' => $this->lifecycleService->participantLeft($meeting, $detail, true),
            'room_destroyed', 'conference_ended', 'meeting_ended', 'end_conference' => $this->lifecycleService->endMeeting(
                $meeting,
                (string) ($payload['reason'] ?? 'moderator_ended'),
                $detail,
                true
            ),
            default => null,
        };

        if (!in_array($event, [
            'participant_joined', 'occupant_joined', 'muc-occupant-joined',
            'participant_left', 'occupant_left', 'muc-occupant-left',
            'room_destroyed', 'conference_ended', 'meeting_ended', 'end_conference',
        ], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unsupported event type.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'meeting_id' => $meeting->id,
            'event' => $event,
        ]);
    }

    private function resolveMeeting(array $payload): ?Meeting
    {
        if (!empty($payload['meeting_id'])) {
            return Meeting::find($payload['meeting_id']);
        }

        $roomName = $payload['room_name'] ?? $payload['room'] ?? null;
        if (!$roomName) {
            return null;
        }

        return Meeting::where('room_name', $roomName)->first();
    }
}
