@extends('tyro-dashboard::layouts.app')

@section('title', 'Edit Meeting')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.my-meetings') }}">My Meetings</a>
<span class="breadcrumb-separator">/</span>
<span>Edit Meeting</span>
@endsection

@section('content')
<style>
.meeting-type-tabs {
    display: flex;
    background: var(--muted);
    border-radius: 10px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 28px;
    width: fit-content;
}
.meeting-type-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9375rem;
    font-weight: 500;
    border: none;
    background: transparent;
    color: var(--muted-foreground);
    transition: all 0.15s;
    white-space: nowrap;
}
.meeting-type-tab.active {
    background: var(--card);
    color: var(--foreground);
    box-shadow: 0 1px 6px rgba(0,0,0,0.1);
}
.form-grid { display: grid; gap: 20px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group label { display: block; margin-bottom: 7px; font-weight: 500; font-size: 0.9375rem; }
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 13px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 0.9375rem;
    background: var(--background);
    color: var(--foreground);
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
}
.form-group input[type="datetime-local"] {
    color-scheme: light;
}
.form-group .help-text { font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 5px; }
.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    margin-top: 8px;
}
.advanced-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--muted-foreground);
    user-select: none;
}
.advanced-toggle svg { transition: transform 0.2s; }
.advanced-toggle.open svg { transform: rotate(180deg); }
@media (max-width: 600px) {
    .form-row-2 { grid-template-columns: 1fr; }
    .meeting-type-tabs { width: 100%; }
    .meeting-type-tab { flex: 1; justify-content: center; }
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Meeting</h1>
            <p class="page-description" style="font-size: 1rem;">Update the details for this upcoming meeting.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="padding: 14px 18px; margin-bottom: 20px; background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; border-radius: 8px;">
        <strong>Success!</strong> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="padding: 14px 18px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 8px;">
        <strong>Please fix the following:</strong>
        <ul style="margin: 6px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body" style="padding: 28px 32px;">
        <form method="POST" action="{{ route('dashboard.meetings.update', $meeting->id) }}">
            @csrf
            @method('PUT')

            <div class="meeting-type-tabs">
                <button type="button" class="meeting-type-tab {{ old('meeting_type', $meetingType) !== 'scheduled' ? 'active' : '' }}"
                    id="tab_instant" onclick="setMeetingType('instant')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Instant Meeting
                </button>
                <button type="button" class="meeting-type-tab {{ old('meeting_type', $meetingType) === 'scheduled' ? 'active' : '' }}"
                    id="tab_scheduled" onclick="setMeetingType('scheduled')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule
                </button>
            </div>

            <input type="hidden" name="meeting_type" id="meeting_type"
                value="{{ old('meeting_type', $meetingType) }}">

            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Meeting Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $meeting->title) }}" required
                           placeholder="e.g. Weekly Team Standup" autofocus>
                </div>

                <div id="scheduled_fields" style="{{ old('meeting_type', $meetingType) === 'scheduled' ? '' : 'display:none;' }}">
                    <div class="form-row-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="start_at">Start Time *</label>
                            <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at', optional($meeting->start_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="end_at">End Time *</label>
                            <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at', optional($meeting->end_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="timezone">Timezone *</label>
                        <select id="timezone" name="timezone" required>
                            @foreach($timezones as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', $defaultTimezone ?? 'UTC') == $tz ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="visibility">Visibility *</label>
                        <select id="visibility" name="visibility" required onchange="toggleOrganizationRequirement()">
                            <option value="link_anyone" {{ old('visibility', $defaultVisibility ?? 'link_anyone') == 'link_anyone' ? 'selected' : '' }}>Anyone with Link</option>
                            <option value="org_only" {{ old('visibility', $defaultVisibility ?? 'link_anyone') == 'org_only' ? 'selected' : '' }}>Organization Only</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="org_field" style="{{ old('visibility', $defaultVisibility) == 'org_only' ? '' : 'display:none;' }}">
                    <label for="organization_id">
                        Organization <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="organization_id" name="organization_id">
                        <option value="">— Select Organization —</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id', $meeting->organization_id) == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description <span style="font-weight: 400; color: var(--muted-foreground);">(optional)</span></label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Add agenda, notes, or other details…">{{ old('description', $meeting->description) }}</textarea>
                </div>

                <div>
                    <div class="advanced-toggle open" id="advancedToggle" onclick="toggleAdvanced()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Advanced options
                    </div>
                    <div id="advancedFields" style="margin-top: 16px;">
                        <div class="form-row-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="join_early_minutes">Join Early (minutes)</label>
                                <input type="number" id="join_early_minutes" name="join_early_minutes"
                                       value="{{ old('join_early_minutes', $meeting->join_early_minutes ?? 10) }}" min="0" max="120">
                                <div class="help-text">Minutes before start time users can join</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="join_late_minutes">Join Late (minutes)</label>
                                <input type="number" id="join_late_minutes" name="join_late_minutes"
                                       value="{{ old('join_late_minutes', $meeting->join_late_minutes ?? 60) }}" min="0" max="240">
                                <div class="help-text">Minutes after end time users can still join</div>
                            </div>
                        </div>

                        <div class="form-row-2" style="margin-top:20px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="password">Meeting Password</label>
                                <input type="password" id="password" name="password" value="" placeholder="Leave blank to keep current password">
                                <div class="help-text">Leave blank to keep the existing password unchanged.</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="max_participants">Max Participants</label>
                                <input type="number" id="max_participants" name="max_participants"
                                       value="{{ old('max_participants', $meeting->max_participants) }}" min="2" max="1000">
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:20px;">
                            <label for="allowed_ips">Allowed IPs</label>
                            <textarea id="allowed_ips" name="allowed_ips" rows="4" placeholder="One IP or CIDR per line">{{ old('allowed_ips', $meeting->allowed_ips) }}</textarea>
                            <div class="help-text">Leave blank to allow all IPs unless IP restriction is enabled.</div>
                        </div>

                        <div class="form-row-2" style="margin-top:20px; align-items:start;">
                            <label style="display:flex; gap:10px; align-items:center; font-weight:500;">
                                <input type="checkbox" name="lobby_enabled" value="1" {{ old('lobby_enabled', $meeting->lobby_enabled) ? 'checked' : '' }}>
                                Enable lobby
                            </label>
                            <label style="display:flex; gap:10px; align-items:center; font-weight:500;">
                                <input type="checkbox" name="ip_restriction_enabled" value="1" {{ old('ip_restriction_enabled', $meeting->ip_restriction_enabled) ? 'checked' : '' }}>
                                Enable IP restriction
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="min-width: 160px;">
                        <span>Update Meeting</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function setMeetingType(type) {
    document.getElementById('meeting_type').value = type;

    const scheduledFields = document.getElementById('scheduled_fields');
    const startInput = document.getElementById('start_at');
    const endInput = document.getElementById('end_at');

    document.getElementById('tab_instant').classList.toggle('active', type === 'instant');
    document.getElementById('tab_scheduled').classList.toggle('active', type === 'scheduled');

    if (type === 'instant') {
        scheduledFields.style.display = 'none';
        startInput.removeAttribute('required');
        endInput.removeAttribute('required');
    } else {
        scheduledFields.style.display = '';
        startInput.setAttribute('required', 'required');
        endInput.setAttribute('required', 'required');
    }
}

function toggleOrganizationRequirement() {
    const visibility = document.getElementById('visibility').value;
    const orgField = document.getElementById('org_field');
    const orgSelect = document.getElementById('organization_id');
    if (visibility === 'org_only') {
        orgField.style.display = '';
        orgSelect.setAttribute('required', 'required');
    } else {
        orgField.style.display = 'none';
        orgSelect.removeAttribute('required');
    }
}

function toggleAdvanced() {
    const fields = document.getElementById('advancedFields');
    const toggle = document.getElementById('advancedToggle');
    const open = fields.style.display === 'none';
    fields.style.display = open ? '' : 'none';
    toggle.classList.toggle('open', open);
}

setMeetingType(document.getElementById('meeting_type').value);
toggleOrganizationRequirement();
</script>
@endsection
