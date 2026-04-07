<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\MeetingEvent;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class EditMeetingController extends Controller
{
    public function edit(Request $request, Meeting $meeting): View
    {
        $this->authorize('update', $meeting);

        if (! $meeting->isUpcomingAt(now())) {
            abort(403, 'Only upcoming meetings can be edited.');
        }

        $user = $request->user();
        $organizations = $this->availableOrganizations($user);
        $timezones = array_combine(
            \DateTimeZone::listIdentifiers(),
            \DateTimeZone::listIdentifiers()
        );

        return view('dashboard.edit-meeting', [
            'meeting' => $meeting,
            'organizations' => $organizations,
            'timezones' => $timezones,
            'defaultTimezone' => $meeting->timezone ?: 'UTC',
            'defaultVisibility' => Meeting::normalizeVisibility($meeting->visibility),
            'meetingType' => $meeting->isInstantMeeting() ? 'instant' : 'scheduled',
        ]);
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('update', $meeting);

        if (! $meeting->isUpcomingAt(now())) {
            return redirect()
                ->route('dashboard.my-meetings')
                ->withErrors(['meeting' => 'Only upcoming meetings can be edited.']);
        }

        $user = $request->user();
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

        if (($data['visibility'] ?? null) === 'org_only' && empty($data['organization_id'])) {
            return redirect()->back()
                ->withErrors(['organization_id' => 'An organization must be selected for organization-only meetings.'])
                ->withInput();
        }

        if ($user?->isOrganizationUser() && $user->organization_id) {
            $data['organization_id'] = $user->organization_id;
        }

        if (($data['organization_id'] ?? null) && ! $this->canUseOrganization($user, (string) $data['organization_id'])) {
            return redirect()->back()
                ->withErrors(['organization_id' => 'You are not allowed to assign this organization.'])
                ->withInput();
        }

        if ($data['meeting_type'] === 'instant') {
            $data['status'] = 'live';
            $data['start_at'] = null;
            $data['end_at'] = null;
        } else {
            $data['status'] = 'scheduled';
        }

        unset($data['meeting_type']);

        $data['lobby_enabled'] = $request->has('lobby_enabled');
        $data['ip_restriction_enabled'] = $request->has('ip_restriction_enabled');
        $data['allow_guests'] = ($data['visibility'] ?? null) === 'link_anyone';

        if (!array_key_exists('password', $data) || $data['password'] === null || $data['password'] === '') {
            unset($data['password']);
        }

        $meeting->update($data);

        return redirect()
            ->route('dashboard.my-meetings')
            ->with('success', 'Meeting updated successfully.');
    }

    public function destroy(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorize('deleteVisible', $meeting);

        if (! $meeting->canBeDeletedAt(now())) {
            return redirect()
                ->route('dashboard.my-meetings')
                ->withErrors(['meeting' => 'This meeting cannot be deleted while it is live or still has active participants.']);
        }

        DB::transaction(function () use ($request, $meeting) {
            MeetingEvent::create([
                'meeting_id' => $meeting->id,
                'type' => 'meeting_deleted',
                'payload' => [
                    'deleted_by_user_id' => $request->user()?->id,
                    'deleted_by_name' => $request->user()?->name,
                    'deleted_at' => now()->toIso8601String(),
                    'meeting_status' => $meeting->status,
                    'active_participant_count' => (int) $meeting->active_participant_count,
                ],
            ]);

            $meeting->delete();
        });

        return redirect()
            ->route('dashboard.my-meetings')
            ->with('success', 'Meeting deleted successfully.');
    }

    private function availableOrganizations($user)
    {
        if ($user && method_exists($user, 'isOrganizationUser') && $user->isOrganizationUser() && $user->organization_id) {
            return Organization::where('id', $user->organization_id)->orderBy('name')->get();
        }

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return Organization::orderBy('name')->get();
        }

        return Organization::query()
            ->when($user?->organization_id, fn ($query) => $query->where('id', $user->organization_id))
            ->orderBy('name')
            ->get();
    }

    private function canUseOrganization($user, string $organizationId): bool
    {
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        return !empty($user?->organization_id)
            && (string) $user->organization_id === $organizationId;
    }
}
