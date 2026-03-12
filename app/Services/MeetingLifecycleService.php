<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingEvent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class MeetingLifecycleService
{
    public function participantJoined(Meeting $meeting, array $payload = [], bool $logEvent = false): Meeting
    {
        return DB::transaction(function () use ($meeting, $payload, $logEvent) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();
            $now = CarbonImmutable::now();

            if ($logEvent) {
                $this->recordEvent($fresh, 'participant_joined', $payload, $now);
            }

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
    }

    public function participantLeft(Meeting $meeting, array $payload = [], bool $logEvent = false): Meeting
    {
        return DB::transaction(function () use ($meeting, $payload, $logEvent) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();
            $now = CarbonImmutable::now();

            if ($logEvent) {
                $this->recordEvent($fresh, 'participant_left', $payload, $now);
            }

            $fresh->active_participant_count = max(0, ((int) $fresh->active_participant_count) - 1);
            $fresh->last_activity_at = $now;
            $fresh->save();

            return $fresh->fresh();
        });
    }

    public function endMeeting(Meeting $meeting, string $reason, array $payload = [], bool $logEvent = false): Meeting
    {
        return DB::transaction(function () use ($meeting, $reason, $payload, $logEvent) {
            /** @var Meeting $fresh */
            $fresh = Meeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();
            $now = CarbonImmutable::now();

            if ($logEvent) {
                $this->recordEvent($fresh, 'meeting_ended', array_merge($payload, ['reason' => $reason]), $now);
            }

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
}
