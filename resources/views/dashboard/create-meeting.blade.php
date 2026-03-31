@extends('tyro-dashboard::layouts.app')

@section('title', 'Create Meeting')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Create Meeting</span>
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
.form-group input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    opacity: 0.85;
    cursor: pointer;
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
            <h1 class="page-title">Create Meeting</h1>
            <p class="page-description" style="font-size: 1rem;">Start instantly or schedule for later.</p>
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
        <form method="POST" action="{{ route('dashboard.create-meeting.store') }}">
            @csrf

            <!-- Meeting Type Tabs -->
            <div class="meeting-type-tabs">
                <button type="button" class="meeting-type-tab {{ old('meeting_type', request('type', 'instant')) !== 'scheduled' ? 'active' : '' }}"
                    id="tab_instant" onclick="setMeetingType('instant')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Instant Meeting
                </button>
                <button type="button" class="meeting-type-tab {{ old('meeting_type', request('type', 'instant')) === 'scheduled' ? 'active' : '' }}"
                    id="tab_scheduled" onclick="setMeetingType('scheduled')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule
                </button>
            </div>

            <input type="hidden" name="meeting_type" id="meeting_type"
                value="{{ old('meeting_type', request('type', 'instant') === 'scheduled' ? 'scheduled' : 'instant') }}">

            <div class="form-grid">
                <!-- Title -->
                <div class="form-group">
                    <label for="title">Meeting Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. Weekly Team Standup" autofocus>
                </div>

                <!-- Scheduled Fields -->
                <div id="scheduled_fields" style="{{ old('meeting_type', request('type', 'instant')) === 'scheduled' ? '' : 'display:none;' }}">
                    <div class="form-row-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="start_at">Start Time *</label>
                            <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at') }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="end_at">End Time *</label>
                            <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at') }}">
                        </div>
                    </div>
                </div>

                <!-- Timezone & Visibility row -->
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
                            {{-- invite_only hidden for v1: not production-complete --}}
                            {{-- <option value="invite_only" {{ old('visibility', $defaultVisibility ?? 'link_anyone') == 'invite_only' ? 'selected' : '' }}>Invite Only</option> --}}
                            <option value="link_anyone" {{ old('visibility', $defaultVisibility ?? 'link_anyone') == 'link_anyone' ? 'selected' : '' }}>Anyone with Link</option>
                            <option value="org_only" {{ old('visibility', $defaultVisibility ?? 'link_anyone') == 'org_only' ? 'selected' : '' }}>Organization Only</option>
                        </select>
                    </div>
                </div>

                <!-- Organization (hidden unless org_only) -->
                <div class="form-group" id="org_field" style="{{ old('visibility') == 'org_only' ? '' : 'display:none;' }}">
                    <label for="organization_id">
                        Organization <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="organization_id" name="organization_id">
                        <option value="">— Select Organization —</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Description (collapsed by default) -->
                <div class="form-group">
                    <label for="description">Description <span style="font-weight: 400; color: var(--muted-foreground);">(optional)</span></label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Add agenda, notes, or other details…">{{ old('description') }}</textarea>
                </div>

                <!-- Advanced Options -->
                <div>
                    <div class="advanced-toggle" id="advancedToggle" onclick="toggleAdvanced()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Advanced options
                    </div>
                    <div id="advancedFields" style="display:none; margin-top: 16px;">
                        <div class="form-row-2">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="join_early_minutes">Join Early (minutes)</label>
                                <input type="number" id="join_early_minutes" name="join_early_minutes"
                                       value="{{ old('join_early_minutes', 10) }}" min="0" max="120">
                                <div class="help-text">Minutes before start time users can join</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="join_late_minutes">Join Late (minutes)</label>
                                <input type="number" id="join_late_minutes" name="join_late_minutes"
                                       value="{{ old('join_late_minutes', 60) }}" min="0" max="240">
                                <div class="help-text">Minutes after end time users can still join</div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="min-width: 160px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" id="submit_icon_instant"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" id="submit_icon_scheduled" style="display:none;"/>
                        </svg>
                        <span id="submit_text">Start Instant Meeting</span>
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
    const submitText = document.getElementById('submit_text');
    const iconInstant = document.getElementById('submit_icon_instant');
    const iconScheduled = document.getElementById('submit_icon_scheduled');

    document.getElementById('tab_instant').classList.toggle('active', type === 'instant');
    document.getElementById('tab_scheduled').classList.toggle('active', type === 'scheduled');

    if (type === 'instant') {
        scheduledFields.style.display = 'none';
        startInput.removeAttribute('required');
        endInput.removeAttribute('required');
        submitText.textContent = 'Start Instant Meeting';
        iconInstant.style.display = '';
        iconScheduled.style.display = 'none';
    } else {
        scheduledFields.style.display = '';
        startInput.setAttribute('required', 'required');
        endInput.setAttribute('required', 'required');
        submitText.textContent = 'Schedule Meeting';
        iconInstant.style.display = 'none';
        iconScheduled.style.display = '';
        // Set default start time to next round hour if empty
        if (!startInput.value) {
            const d = new Date();
            d.setHours(d.getHours() + 1, 0, 0, 0);
            startInput.value = toLocalDatetimeString(d);
            const e = new Date(d.getTime() + 60 * 60000);
            endInput.value = toLocalDatetimeString(e);
        }
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

function applyBrowserTimezoneDefault() {
    const timezoneSelect = document.getElementById('timezone');
    const hasOldTimezone = @json(old('timezone')) !== null;

    if (!timezoneSelect || hasOldTimezone || !window.Intl || !Intl.DateTimeFormat) {
        return;
    }

    const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    if (!browserTimezone) {
        return;
    }

    const existingOption = Array.from(timezoneSelect.options).find(option => option.value === browserTimezone);

    if (existingOption) {
        timezoneSelect.value = browserTimezone;
        return;
    }

    const fallbackOption = document.createElement('option');
    fallbackOption.value = browserTimezone;
    fallbackOption.textContent = `${browserTimezone} (Browser)`;
    timezoneSelect.prepend(fallbackOption);
    timezoneSelect.value = browserTimezone;
}

function toLocalDatetimeString(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const h = String(date.getHours()).padStart(2, '0');
    const min = String(date.getMinutes()).padStart(2, '0');
    return `${y}-${m}-${d}T${h}:${min}`;
}

function enforceStartTimeMin() {
    const startInput = document.getElementById('start_at');
    const now = new Date();
    now.setMinutes(now.getMinutes() + 10, 0, 0);
    startInput.min = toLocalDatetimeString(now);
}

// Initialize
applyBrowserTimezoneDefault();
enforceStartTimeMin();
setMeetingType(document.getElementById('meeting_type').value);
toggleOrganizationRequirement();
</script>
@endsection
