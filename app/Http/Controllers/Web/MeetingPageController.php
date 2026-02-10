<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingPageController extends Controller
{
    public function show(Request $request, Meeting $meeting): View
    {
        $now = Carbon::now();
        $canJoin = $meeting->canJoinAt($now);
        
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
}
