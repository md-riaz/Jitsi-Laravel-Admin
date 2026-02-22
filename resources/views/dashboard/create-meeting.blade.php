@extends('tyro-dashboard::layouts.app')

@section('title', 'Create Meeting')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Create Meeting</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create Meeting</h1>
            <p class="page-description" style="font-size: 1rem;">Create an instant meeting or schedule a meeting for later.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="padding: 16px; margin-bottom: 20px; background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; border-radius: 6px;">
        <strong>Success!</strong> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="padding: 16px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong>
        <ul style="margin: 8px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.create-meeting.store') }}">
            @csrf

            <div style="display: grid; gap: 20px;">
                <!-- Meeting Type Selection -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Meeting Type</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="meeting_type" value="instant" id="instant_meeting" {{ old('meeting_type', 'instant') == 'instant' ? 'checked' : '' }} onchange="toggleMeetingType()" style="cursor: pointer;">
                            <span>Instant Meeting (Start Now)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="meeting_type" value="scheduled" id="scheduled_meeting" {{ old('meeting_type') == 'scheduled' ? 'checked' : '' }} onchange="toggleMeetingType()" style="cursor: pointer;">
                            <span>Scheduled Meeting</span>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="title" style="display: block; margin-bottom: 8px; font-weight: 500;">Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           placeholder="Enter meeting title">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" style="display: block; margin-bottom: 8px; font-weight: 500;">Description</label>
                    <textarea id="description" name="description" rows="4"
                              style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                              placeholder="Enter meeting description (optional)">{{ old('description') }}</textarea>
                </div>

                <!-- Organization -->
                <div>
                    <label for="organization_id" style="display: block; margin-bottom: 8px; font-weight: 500;">
                        Organization
                        <span id="org_required_indicator" style="color: #ef4444; display: none;">*</span>
                        <span id="org_optional_indicator" style="color: #6b7280; font-weight: 400;">(Optional)</span>
                    </label>
                    <select id="organization_id" name="organization_id"
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        <option value="">Personal Meeting (No Organization)</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                    <small id="org_help_text" style="color: #6b7280;">Leave unselected to create a personal meeting, or select an organization to create an organization meeting</small>
                    <small id="org_required_text" style="color: #ef4444; display: none;">An organization is required when visibility is set to "Organization Only"</small>
                </div>

                <!-- Scheduled Meeting Fields -->
                <div id="scheduled_fields" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- Start Time -->
                        <div>
                            <label for="start_at" style="display: block; margin-bottom: 8px; font-weight: 500;">Start Time</label>
                            <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at') }}"
                                   style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- End Time -->
                        <div>
                            <label for="end_at" style="display: block; margin-bottom: 8px; font-weight: 500;">End Time</label>
                            <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at') }}"
                                   style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        </div>
                    </div>
                </div>

                <!-- Timezone -->
                <div>
                    <label for="timezone" style="display: block; margin-bottom: 8px; font-weight: 500;">Timezone *</label>
                    <select id="timezone" name="timezone" required
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        @foreach($timezones as $tz => $label)
                            <option value="{{ $tz }}" {{ old('timezone', 'UTC') == $tz ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Visibility -->
                <div>
                    <label for="visibility" style="display: block; margin-bottom: 8px; font-weight: 500;">Visibility *</label>
                    <select id="visibility" name="visibility" required
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        <option value="invite_only" {{ old('visibility', 'invite_only') == 'invite_only' ? 'selected' : '' }}>Invite Only</option>
                        <option value="link_anyone" {{ old('visibility') == 'link_anyone' ? 'selected' : '' }}>Anyone with Link</option>
                        <option value="org_only" {{ old('visibility') == 'org_only' ? 'selected' : '' }}>Organization Only</option>
                    </select>
                </div>

                <!-- Advanced Options -->
                <div>
                    <details style="border: 1px solid #d1d5db; border-radius: 6px; padding: 16px;">
                        <summary style="cursor: pointer; font-weight: 500; margin-bottom: 16px;">Advanced Options</summary>
                        <div style="display: grid; gap: 20px; margin-top: 16px;">
                            <!-- Join Window Settings -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label for="join_early_minutes" style="display: block; margin-bottom: 8px; font-weight: 500;">Join Early (minutes)</label>
                                    <input type="number" id="join_early_minutes" name="join_early_minutes" value="{{ old('join_early_minutes', 10) }}" min="0" max="120"
                                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                    <small style="color: #6b7280;">How many minutes before start time can users join</small>
                                </div>

                                <div>
                                    <label for="join_late_minutes" style="display: block; margin-bottom: 8px; font-weight: 500;">Join Late (minutes)</label>
                                    <input type="number" id="join_late_minutes" name="join_late_minutes" value="{{ old('join_late_minutes', 60) }}" min="0" max="240"
                                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                    <small style="color: #6b7280;">How many minutes after end time can users still join</small>
                                </div>
                            </div>

                            <!-- Security Settings -->
                            <div style="border-top: 1px solid #e5e7eb; padding-top: 16px;">
                                <h3 style="font-weight: 600; margin-bottom: 16px; color: #374151;">Security Settings</h3>

                                <div style="display: grid; gap: 16px;">
                                    <!-- Password -->
                                    <div>
                                        <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Meeting Password (Optional)</label>
                                        <input type="password" id="password" name="password" value="{{ old('password') }}"
                                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                                               placeholder="Leave blank for no password">
                                        <small style="color: #6b7280;">Require participants to enter a password to join</small>
                                    </div>

                                    <!-- Lobby Enable/Disable -->
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="lobby_enabled" value="1" {{ old('lobby_enabled', true) ? 'checked' : '' }}
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="font-weight: 500;">Enable Lobby (Waiting Room)</span>
                                        </label>
                                        <small style="color: #6b7280; margin-left: 26px; display: block;">Participants wait in a lobby until admitted by a moderator</small>
                                    </div>

                                    <!-- Allow Guests -->
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="allow_guests" value="1" {{ old('allow_guests', true) ? 'checked' : '' }}
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="font-weight: 500;">Allow Guest Users</span>
                                        </label>
                                        <small style="color: #6b7280; margin-left: 26px; display: block;">Allow users without accounts to join the meeting</small>
                                    </div>

                                    <!-- Max Participants -->
                                    <div>
                                        <label for="max_participants" style="display: block; margin-bottom: 8px; font-weight: 500;">Maximum Participants (Optional)</label>
                                        <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants') }}" min="2" max="1000"
                                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                                               placeholder="Leave blank for unlimited">
                                        <small style="color: #6b7280;">Limit the number of participants who can join</small>
                                    </div>

                                    <!-- IP Restriction -->
                                    <div>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px;">
                                            <input type="checkbox" name="ip_restriction_enabled" value="1" {{ old('ip_restriction_enabled', false) ? 'checked' : '' }}
                                                   id="ip_restriction_enabled"
                                                   style="width: 18px; height: 18px; cursor: pointer;">
                                            <span style="font-weight: 500;">Enable IP Restriction</span>
                                        </label>
                                        <textarea id="allowed_ips" name="allowed_ips" rows="3"
                                                  style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                                                  placeholder="Enter allowed IP addresses or CIDR ranges (one per line)&#10;Example:&#10;192.168.1.100&#10;10.0.0.0/8">{{ old('allowed_ips') }}</textarea>
                                        <small style="color: #6b7280;">One IP address or CIDR range per line (e.g., 192.168.1.0/24)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <a href="{{ route('dashboard.my-meetings') }}" 
                       style="padding: 10px 24px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151; background: white;">
                        Cancel
                    </a>
                    <button type="submit" 
                            style="padding: 10px 24px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        <span id="submit_text">Create Instant Meeting</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMeetingType() {
    const isInstant = document.getElementById('instant_meeting').checked;
    const scheduledFields = document.getElementById('scheduled_fields');
    const submitText = document.getElementById('submit_text');
    const startInput = document.getElementById('start_at');
    const endInput = document.getElementById('end_at');

    if (isInstant) {
        scheduledFields.style.display = 'none';
        submitText.textContent = 'Create Instant Meeting';
        startInput.removeAttribute('required');
        endInput.removeAttribute('required');
    } else {
        scheduledFields.style.display = 'block';
        submitText.textContent = 'Schedule Meeting';
        startInput.setAttribute('required', 'required');
        endInput.setAttribute('required', 'required');
    }
}

function toggleOrganizationRequirement() {
    const visibility = document.getElementById('visibility').value;
    const orgSelect = document.getElementById('organization_id');
    const orgRequiredIndicator = document.getElementById('org_required_indicator');
    const orgOptionalIndicator = document.getElementById('org_optional_indicator');
    const orgHelpText = document.getElementById('org_help_text');
    const orgRequiredText = document.getElementById('org_required_text');

    if (visibility === 'org_only') {
        // Organization is required for org_only visibility
        orgSelect.setAttribute('required', 'required');
        orgRequiredIndicator.style.display = 'inline';
        orgOptionalIndicator.style.display = 'none';
        orgHelpText.style.display = 'none';
        orgRequiredText.style.display = 'block';
    } else {
        // Organization is optional for other visibility options
        orgSelect.removeAttribute('required');
        orgRequiredIndicator.style.display = 'none';
        orgOptionalIndicator.style.display = 'inline';
        orgHelpText.style.display = 'block';
        orgRequiredText.style.display = 'none';
    }
}

// Initialize on page load
toggleMeetingType();
toggleOrganizationRequirement();

// Add event listener for visibility changes
document.getElementById('visibility').addEventListener('change', toggleOrganizationRequirement);
</script>
@endsection
