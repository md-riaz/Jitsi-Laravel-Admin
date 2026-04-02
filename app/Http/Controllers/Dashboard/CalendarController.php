<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return view('dashboard.calendar');
    }

    public function events(Request $request)
    {
        $user = Auth::user();

        // Get start and end dates from request (FullCalendar sends these)
        $start = $request->input('start');
        $end = $request->input('end');

        // Get user's meetings (either created by user or user is a participant)
        $meetings = Meeting::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhereHas('participants', function ($q) use ($user) {
                      $q->where('user_id', $user->id);
                  });
        })
        ->when($start, function ($query) use ($start) {
            return $query->where('start_at', '>=', $start);
        })
        ->when($end, function ($query) use ($end) {
            return $query->where('start_at', '<=', $end);
        })
        ->get();

        // Format meetings for FullCalendar
        $events = $meetings->map(function ($meeting) {
            $isInstant = $meeting->isInstantMeeting();
            $start = $isInstant ? ($meeting->actual_started_at ?? $meeting->start_at ?? now()) : $meeting->start_at;
            $end = $isInstant ? ($meeting->actual_ended_at ?? $meeting->end_at ?? $start->copy()->addHour()) : $meeting->end_at;

            return [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'url' => route('meeting.show', $meeting->id),
                'backgroundColor' => $meeting->canJoinAt(now()) ? '#10b981' : '#667eea',
                'borderColor' => $meeting->canJoinAt(now()) ? '#10b981' : '#667eea',
                'extendedProps' => [
                    'description' => $meeting->description,
                    'status' => $meeting->canJoinAt(now()) ? 'live' : 'upcoming',
                    'isInstant' => $isInstant,
                ],
            ];
        });

        return response()->json($events);
    }
}
