<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Meeting;
use App\Models\MeetingEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MyMeetingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $upcomingMeetings = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->where(function ($query) {
            // Include instant meetings (null end_at) or meetings with end_at in the future
            $query->whereNull('end_at')
                ->orWhere('end_at', '>', now());
        })
        ->orderBy('start_at')
        ->with(['organization', 'creator', 'participants'])
        ->get();

        $pastMeetings = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })
        ->whereNotNull('end_at')
        ->where('end_at', '<=', now())
        ->orderByDesc('start_at')
        ->with(['organization', 'creator', 'participants'])
        ->limit(10)
        ->get();

        $meetingIds = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
        })->pluck('id');

        $events = MeetingEvent::whereIn('meeting_id', $meetingIds)->get();
        $joinEvents = $events->where('type', 'participant_joined');

        $durations = $pastMeetings->map(function ($m) {
            if ($m->start_at && $m->end_at) {
                return $m->start_at->diffInMinutes($m->end_at);
            }
            return null;
        })->filter();

        $analytics = [
            'total_meetings' => $meetingIds->count(),
            'live_now' => $upcomingMeetings->filter(fn($m) => $m->canJoinAt(now()))->count(),
            'avg_participants' => $pastMeetings->count() > 0
                ? round($pastMeetings->avg(fn($m) => $m->participants->count()), 1)
                : 0,
            'avg_duration_minutes' => $durations->count() > 0 ? round($durations->avg(), 1) : 0,
            'join_events_30d' => $joinEvents->filter(fn($e) => $e->created_at >= now()->subDays(30))->count(),
        ];

        return view('dashboard.my-meetings', compact('upcomingMeetings', 'pastMeetings', 'analytics'));
    }
}
