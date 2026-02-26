<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MeetingPageController extends Controller
{
    public function __construct(
        private CalendarService $calendarService
    ) {}

    public function show(Request $request, Meeting $meeting): View
    {
        $now = Carbon::now();
        $canJoin = $meeting->canJoinAt($now);

        // Handle instant meetings (no fixed start/end times)
        if ($meeting->isInstantMeeting()) {
            return view('meeting.show', [
                'meeting' => $meeting,
                'canJoin' => $meeting->status === 'live',
                'status' => $meeting->status === 'live' ? 'live' : 'ended',
                'opensAt' => $now,
                'closesAt' => $now,
                'now' => $now,
            ]);
        }

        $opensAt = $meeting->start_at->copy()->subMinutes($meeting->join_early_minutes);
        $closesAt = $meeting->end_at->copy()->addMinutes($meeting->join_late_minutes);

        $status = 'ended';
        if ($now->lt($opensAt)) {
            $status = 'not_started';
        } elseif ($now->between($opensAt, $closesAt)) {
            $status = 'live';
        }

        return view('meeting.show', [
            'meeting' => $meeting,
            'canJoin' => $canJoin,
            'status' => $status,
            'opensAt' => $opensAt,
            'closesAt' => $closesAt,
            'now' => $now,
        ]);
    }

    public function downloadIcs(Meeting $meeting): Response
    {
        $icsContent = $this->calendarService->generateIcs($meeting);
        $filename = \Str::slug($meeting->title) . '.ics';

        return response($icsContent, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
