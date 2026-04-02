<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\MeetingParticipant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingSummaryController extends Controller
{
    public function show(Request $request, Meeting $meeting)
    {
        $this->authorize('manage', $meeting);

        $events = MeetingEvent::where('meeting_id', $meeting->id)
            ->orderBy('created_at')
            ->get();

        $joinEvents = $events->where('type', 'participant_joined')->values();
        $leaveEvents = $events->where('type', 'participant_left')->values();

        $firstJoin = optional($joinEvents->first())->created_at;
        $lastLeave = optional($leaveEvents->last())->created_at ?? optional($events->last())->created_at;

        $timeline = $events->map(function ($e) {
            return [
                'type' => $e->type,
                'time' => $e->created_at,
                'payload' => $e->payload,
            ];
        });

        $attendanceRows = $this->buildAttendanceRows($meeting, $events);

        $kpis = [
            'total_events' => $events->count(),
            'join_events' => $joinEvents->count(),
            'leave_events' => $leaveEvents->count(),
            'unique_participants' => $attendanceRows->count(),
            'first_join' => $firstJoin,
            'last_leave' => $lastLeave,
            'duration_minutes' => ($firstJoin && $lastLeave) ? Carbon::parse($firstJoin)->diffInMinutes(Carbon::parse($lastLeave)) : null,
            'peak_participants' => $this->calculatePeakParticipants($events),
        ];

        return view('dashboard.meeting-summary', [
            'meeting' => $meeting,
            'kpis' => $kpis,
            'timeline' => $timeline,
            'attendanceRows' => $attendanceRows,
        ]);
    }

    public function exportParticipants(Request $request, Meeting $meeting): StreamedResponse
    {
        $this->authorize('manage', $meeting);

        $rows = $this->buildAttendanceRows($meeting, MeetingEvent::where('meeting_id', $meeting->id)->orderBy('created_at')->get());
        $filename = "meeting_{$meeting->id}_attendance.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'identity', 'joined_at', 'left_at', 'duration_minutes']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['name'],
                    $r['identity'],
                    $r['joined_at'],
                    $r['left_at'],
                    $r['duration_minutes'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportEvents(Request $request, Meeting $meeting): StreamedResponse
    {
        $this->authorize('manage', $meeting);

        $events = MeetingEvent::where('meeting_id', $meeting->id)->orderBy('created_at')->get();
        $filename = "meeting_{$meeting->id}_events.csv";

        return response()->streamDownload(function () use ($events) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['time', 'type', 'payload_json']);
            foreach ($events as $e) {
                fputcsv($out, [
                    (string) $e->created_at,
                    $e->type,
                    json_encode($e->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildAttendanceRows(Meeting $meeting, Collection $events): Collection
    {
        $participants = MeetingParticipant::where('meeting_id', $meeting->id)->get();
        $rows = collect();

        foreach ($participants as $p) {
            $joined = $p->joined_at;
            $left = $p->left_at;
            $rows->push([
                'name' => $p->display_name ?: 'Unknown',
                'identity' => $p->email ?: ('user:' . ($p->user_id ?? 'n/a')),
                'joined_at' => $joined,
                'left_at' => $left,
                'duration_minutes' => ($joined && $left) ? Carbon::parse($joined)->diffInMinutes(Carbon::parse($left)) : null,
            ]);
        }

        // Fallback from events for guests not persisted in participant table
        $eventGroups = $events->whereIn('type', ['participant_joined', 'participant_left'])
            ->groupBy(function ($e) {
                $payload = is_array($e->payload) ? $e->payload : [];

                return (string) ($payload['user_id'] ?? (($payload['user_name'] ?? 'Guest') . '|' . ($payload['ip_address'] ?? 'n/a')));
            });

        foreach ($eventGroups as $identity => $group) {
            $already = $rows->firstWhere('identity', $identity);
            if ($already) {
                continue;
            }

            $firstEvent = $group->first();
            $firstPayload = is_array($firstEvent?->payload) ? $firstEvent->payload : [];
            $join = optional($group->firstWhere('type', 'participant_joined'))->created_at;
            $leave = optional($group->lastWhere('type', 'participant_left'))->created_at;
            $name = (string) ($firstPayload['user_name'] ?? 'Guest');

            $rows->push([
                'name' => $name,
                'identity' => $identity,
                'joined_at' => $join,
                'left_at' => $leave,
                'duration_minutes' => ($join && $leave) ? Carbon::parse($join)->diffInMinutes(Carbon::parse($leave)) : null,
            ]);
        }

        return $rows->sortBy('name')->values();
    }

    private function calculatePeakParticipants(Collection $events): int
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
