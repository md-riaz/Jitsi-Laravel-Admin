<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MeetingLifecycleService
{
    public function participantJoined(Meeting $meeting, array $payload = [], bool $logEvent = false): Meeting
    {
        $now = CarbonImmutable::now();

        $updated = DB::transaction(function () use ($meeting, $now) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();

            $fresh->active_participant_count = max(0, (int) $fresh->active_participant_count) + 1;
            $fresh->last_activity_at = $now;

            if (!$fresh->actual_started_at) {
                $fresh->actual_started_at = $now;
            }

            if ($fresh->status !== 'live') {
                $fresh->status = 'live';
            }

            if ($fresh->isInstantMeeting() && !$fresh->start_at) {
                $fresh->start_at = $fresh->actual_started_at;
            }

            $fresh->actual_ended_at = null;
            $fresh->ended_reason = null;
            $fresh->save();

            return $fresh->fresh();
        });

        if ($logEvent) {
            $this->recordEventSafely($updated, 'participant_joined', $payload, $now);
        }

        return $updated;
    }

    public function participantLeft(Meeting $meeting, array $payload = [], bool $logEvent = false): Meeting
    {
        $now = CarbonImmutable::now();

        $updated = DB::transaction(function () use ($meeting, $now) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();

            $fresh->active_participant_count = max(0, ((int) $fresh->active_participant_count) - 1);
            $fresh->last_activity_at = $now;
            $fresh->save();

            return $fresh->fresh();
        });

        if ($logEvent) {
            $this->recordEventSafely($updated, 'participant_left', $payload, $now);
        }

        return $updated;
    }

    public function endMeeting(Meeting $meeting, string $reason, array $payload = [], bool $logEvent = false): Meeting
    {
        $now = CarbonImmutable::now();

        $updated = DB::transaction(function () use ($meeting, $reason, $now) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();

            $fresh->status = 'ended';
            $fresh->active_participant_count = 0;
            $fresh->last_activity_at = $now;
            $fresh->actual_ended_at = $now;
            $fresh->ended_reason = $reason;

            if (!$fresh->actual_started_at) {
                $fresh->actual_started_at = $fresh->start_at ?: $now;
            }

            if (!$fresh->end_at || $fresh->end_at->greaterThan($now)) {
                $fresh->end_at = $now;
            }

            $fresh->save();

            return $fresh->fresh();
        });

        if ($logEvent) {
            $this->recordEventSafely($updated, 'meeting_ended', array_merge($payload, ['reason' => $reason]), $now);
        }

        return $updated;
    }

    public function cleanupEmptyInstantMeetings(int $graceSeconds): int
    {
        $cutoff = CarbonImmutable::now()->subSeconds($graceSeconds);

        $meetings = Meeting::query()
            ->whereNull('actual_ended_at')
            ->where('status', 'live')
            ->where('active_participant_count', 0)
            ->whereNull('end_at')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($meetings as $meeting) {
            $this->endMeeting($meeting, 'empty_room', [
                'grace_seconds' => $graceSeconds,
                'last_activity_at' => optional($meeting->last_activity_at)?->toIso8601String(),
            ], true);
            $count++;
        }

        return $count;
    }

    private function recordEvent(Meeting $meeting, string $type, array $payload, CarbonImmutable $now): void
    {
        MeetingEvent::create([
            'meeting_id' => $meeting->id,
            'type' => $type,
            'payload' => array_merge($payload, [
                'recorded_at' => $now->toIso8601String(),
            ]),
        ]);
    }

    private function recordEventSafely(Meeting $meeting, string $type, array $payload, CarbonImmutable $now): void
    {
        try {
            $this->recordEvent($meeting, $type, $payload, $now);
        } catch (Throwable $e) {
            Log::warning('Failed to persist meeting lifecycle event', [
                'meeting_id' => $meeting->id,
                'room_name' => $meeting->room_name,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
