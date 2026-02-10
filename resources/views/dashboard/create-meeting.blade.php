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
                    <label for="organization_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Organization *</label>
                    <select id="organization_id" name="organization_id" required
                            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                        <option value="">Select Organization</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
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
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 16px;">
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
                    </details>
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <a href="{{ route('dashboard.my-meetings') }}" 
                       style="padding: 10px 24px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151; background: white;">
                        Cancel
                    </a>
                    <button type="submit" 
                            style="padding: 10px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
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

// Initialize on page load
toggleMeetingType();
</script>
@endsection
