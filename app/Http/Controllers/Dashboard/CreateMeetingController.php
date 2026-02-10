<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateMeetingController extends Controller
{
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $timezones = [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US)',
            'America/Chicago' => 'Central Time (US)',
            'America/Denver' => 'Mountain Time (US)',
            'America/Los_Angeles' => 'Pacific Time (US)',
            'America/Toronto' => 'Toronto',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Dubai' => 'Dubai',
            'Asia/Kolkata' => 'Kolkata',
            'Asia/Singapore' => 'Singapore',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Australia/Sydney' => 'Sydney',
        ];

        return view('dashboard.create-meeting', compact('organizations', 'timezones'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
            'meeting_type' => 'required|in:instant,scheduled',
            'start_at' => 'required_if:meeting_type,scheduled|nullable|date|after_or_equal:now',
            'end_at' => 'required_if:meeting_type,scheduled|nullable|date|after:start_at',
            'timezone' => 'required|string',
            'visibility' => 'required|in:invite_only,link_anyone,org_only',
            'join_early_minutes' => 'nullable|integer|min:0|max:120',
            'join_late_minutes' => 'nullable|integer|min:0|max:240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // For instant meetings, set status to live and clear start/end times
        if ($data['meeting_type'] === 'instant') {
            $data['status'] = 'live';
            $data['start_at'] = null;
            $data['end_at'] = null;
        } else {
            $data['status'] = 'scheduled';
        }
        
        unset($data['meeting_type']);
        $data['created_by'] = Auth::id();

        $meeting = Meeting::create($data);

        return redirect()
            ->route('dashboard.my-meetings')
            ->with('success', 'Meeting created successfully! Meeting link: ' . url("/meet/{$meeting->id}"));
    }
}
