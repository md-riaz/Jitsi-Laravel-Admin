<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\MeetingParticipant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingDiagnosticsController extends Controller
{
    public function show(Request $request, Meeting $meeting): View
    {
        $visibility = Meeting::normalizeVisibility($meeting->visibility);

        $recentDenials = MeetingEvent::where('meeting_id', $meeting->id)
            ->whereIn('type', [
                'join_denied',
                'admission_requested',
                'rejected',
            ])
            ->latest()
            ->limit(25)
            ->get();

        $pendingAdmissions = MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('invite_status', 'pending')
            ->whereNull('user_id')
            ->latest('updated_at')
            ->get();

        return view('dashboard.meeting-diagnostics', [
            'meeting' => $meeting,
            'visibility' => $visibility,
            'recentDenials' => $recentDenials,
            'pendingAdmissions' => $pendingAdmissions,
        ]);
    }
}
