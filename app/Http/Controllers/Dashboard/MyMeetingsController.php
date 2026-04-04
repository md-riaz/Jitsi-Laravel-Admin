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

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $isOrgAdmin = method_exists($user, 'hasRole') && $user->hasRole('org-admin') && !$isSuperAdmin;

        $baseQuery = Meeting::query();

        if ($isSuperAdmin) {
            // platform-wide visibility
        } elseif ($isOrgAdmin && $user->organization_id) {
            $baseQuery->where('organization_id', $user->organization_id);
        } else {
            $baseQuery->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('participants', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            });
        }

        $allMeetings = (clone $baseQuery)
            ->orderBy('start_at')
            ->with(['organization', 'creator', 'participants'])
            ->get();

        $now = now();
        $liveMeetings = $allMeetings->filter(fn ($meeting) => $meeting->isLiveNow($now))->values();
        $upcomingMeetings = $allMeetings->filter(fn ($meeting) => $meeting->isUpcomingAt($now))->values();
        $pastMeetings = $allMeetings->filter(fn ($meeting) => $meeting->isPastAt($now))
            ->sortByDesc('start_at')
            ->take(10)
            ->values();

        $meetingIds = $allMeetings->pluck('id');

        $events = MeetingEvent::whereIn('meeting_id', $meetingIds)->get();
        $joinEvents = $events->where('type', 'participant_joined');

        $durations = $pastMeetings->map(function ($m) {
            if ($m->start_at && $m->end_at) {
                return $m->start_at->diffInMinutes($m->end_at);
            }
            return null;
        })->filter();

        $totalMeetings = $isSuperAdmin || $isOrgAdmin
            ? $meetingIds->count()
            : (clone $baseQuery)->where('created_by', $user->id)->count();

        $analytics = [
            'total_meetings' => $totalMeetings,
            'live_now' => $liveMeetings->count(),
            'avg_participants' => $pastMeetings->count() > 0
                ? round($pastMeetings->avg(fn($m) => (int) $m->active_participant_count), 1)
                : 0,
            'avg_duration_minutes' => $durations->count() > 0 ? round($durations->avg(), 1) : 0,
            'join_events_30d' => $joinEvents->filter(fn($e) => $e->created_at >= now()->subDays(30))->count(),
        ];

        return view('dashboard.my-meetings', compact('liveMeetings', 'upcomingMeetings', 'pastMeetings', 'analytics'));
    }
}
