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
        $user = Auth::user();

        // For organization users, get their organization
        // For single users, get all organizations (for optional selection)
        if ($user->isOrganizationUser() && $user->organization_id) {
            $organizations = Organization::where('id', $user->organization_id)->orderBy('name')->get();
        } else {
            $organizations = Organization::orderBy('name')->get();
        }

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
            'organization_id' => 'required_if:visibility,org_only|nullable|exists:organizations,id',
            'meeting_type' => 'required|in:instant,scheduled',
            'start_at' => 'required_if:meeting_type,scheduled|nullable|date',
            'end_at' => 'required_if:meeting_type,scheduled|nullable|date|after:start_at',
            'timezone' => 'required|string',
            'visibility' => 'required|in:invite_only,link_anyone,org_only',
            'join_early_minutes' => 'nullable|integer|min:0|max:120',
            'join_late_minutes' => 'nullable|integer|min:0|max:240',
            'password' => 'nullable|string|min:4|max:255',
            'lobby_enabled' => 'nullable|boolean',
            'allow_guests' => 'nullable|boolean',
            'max_participants' => 'nullable|integer|min:2|max:1000',
            'allowed_ips' => 'nullable|string',
            'ip_restriction_enabled' => 'nullable|boolean',
        ], [
            'organization_id.required_if' => 'An organization must be selected when visibility is set to "Organization Only".',
            'password.min' => 'Password must be at least 4 characters.',
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

        // Handle checkbox fields properly - unchecked boxes don't send values
        // So we treat absence as false, not default to true
        $data['lobby_enabled'] = $request->has('lobby_enabled');
        $data['allow_guests'] = $request->has('allow_guests');
        $data['ip_restriction_enabled'] = $request->has('ip_restriction_enabled');

        $meeting = Meeting::create($data);

        return redirect()
            ->route('dashboard.my-meetings')
            ->with('success', 'Meeting created successfully! Meeting link: ' . url("/meet/{$meeting->id}"));
    }
}
