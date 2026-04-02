@extends('tyro-dashboard::layouts.app')

@section('title', 'My Meetings')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>My Meetings</span>
@endsection

@section('content')
<style>
.meeting-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 0.25rem;
}

.meeting-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.125rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    transition: box-shadow 0.15s, border-color 0.15s;
}

.meeting-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.09);
    border-color: color-mix(in srgb, var(--primary), transparent 75%);
}

.meeting-card.live {
    border-left: 3px solid var(--success);
}

.meeting-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.625rem;
}

.meeting-card-title {
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--foreground);
    line-height: 1.3;
}

.meeting-card-desc {
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    margin-top: 2px;
}

.meeting-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    align-items: center;
}

.meeting-card-meta svg {
    width: 13px;
    height: 13px;
    display: inline;
    vertical-align: middle;
    margin-right: 3px;
}

.meeting-inline-icon {
    display: inline;
    vertical-align: middle;
    margin-right: 3px;
}

.meeting-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.625rem;
    border-top: 1px solid var(--border);
    gap: 0.5rem;
}

.meeting-card-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.badge-live,
.badge-upcoming,
.badge-ended,
.badge-instant {
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-live {
    background: color-mix(in srgb, var(--success), transparent 84%);
    color: color-mix(in srgb, var(--success), black 25%);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-live::before {
    content: '';
    width: 6px;
    height: 6px;
    background: color-mix(in srgb, var(--success), black 18%);
    border-radius: 50%;
    display: inline-block;
    animation: pulse-dot 1.5s infinite;
}

@keyframes pulse-dot {
    0%,100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.badge-upcoming {
    background: color-mix(in srgb, var(--warning), transparent 85%);
    color: color-mix(in srgb, var(--warning), black 35%);
}

.badge-ended {
    background: var(--muted);
    color: var(--muted-foreground);
}

.badge-instant {
    background: color-mix(in srgb, var(--primary), transparent 88%);
    color: var(--primary);
}

.empty-meetings {
    text-align: center;
    padding: 3rem 1.25rem;
    color: var(--muted-foreground);
}

.empty-meetings svg {
    width: 52px;
    height: 52px;
    margin: 0 auto 0.875rem;
    display: block;
    opacity: 0.35;
}

.empty-meetings p {
    margin: 0 0 1rem 0;
    font-size: 0.9375rem;
}

.meetings-header-actions {
    display: flex;
    gap: 0.625rem;
}

.meeting-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.meeting-stat-label {
    font-size: 0.75rem;
    color: var(--muted-foreground);
}

.meeting-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.section-count {
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    background: var(--muted);
    padding: 2px 10px;
    border-radius: 20px;
}

.muted-small {
    color: var(--muted-foreground);
    font-size: 0.875rem;
}

.table-cell-right {
    text-align: right;
}

.no-padding {
    padding: 0;
}

.card-spaced {
    margin-bottom: 1.25rem;
}

.copy-btn {
    position: relative;
}

.copy-btn .copied-tip {
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: white;
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
}

.copy-btn.did-copy .copied-tip {
    opacity: 1;
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Meetings</h1>
            <p class="page-description">Manage your upcoming and past meetings.</p>
        </div>
        <div class="meetings-header-actions">
            <a href="{{ route('dashboard.calendar') }}" class="btn btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Calendar
            </a>
            <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Meeting
            </a>
        </div>
    </div>
</div>

<div class="meeting-stats-grid">
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Total Meetings</div><div class="meeting-stat-value">{{ $analytics['total_meetings'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Live Now</div><div class="meeting-stat-value">{{ $analytics['live_now'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Avg Participants</div><div class="meeting-stat-value">{{ $analytics['avg_participants'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Avg Duration (min)</div><div class="meeting-stat-value">{{ $analytics['avg_duration_minutes'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Join Events (30d)</div><div class="meeting-stat-value">{{ $analytics['join_events_30d'] ?? 0 }}</div></div></div>
</div>

<div class="card card-spaced">
    <div class="card-header">
        <h3 class="card-title">Upcoming Meetings</h3>
        <span class="section-count">{{ $upcomingMeetings->count() }}</span>
    </div>
    <div class="card-body">
        @if($upcomingMeetings->isEmpty())
            <div class="empty-meetings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p>No upcoming meetings scheduled.</p>
                <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary btn-sm">Create a Meeting</a>
            </div>
        @else
            <div class="meeting-cards-grid">
                @foreach($upcomingMeetings as $meeting)
                @php $isLive = $meeting->canJoinAt(now()); @endphp
                <div class="meeting-card {{ $isLive ? 'live' : '' }}">
                    <div class="meeting-card-top">
                        <div>
                            <div class="meeting-card-title">{{ $meeting->title }}</div>
                            @if($meeting->description)
                                <div class="meeting-card-desc">{{ Str::limit($meeting->description, 70) }}</div>
                            @endif
                        </div>
                        @if($isLive)
                            <span class="badge-live">Live</span>
                        @elseif($meeting->isInstantMeeting())
                            <span class="badge-instant">Instant</span>
                        @else
                            <span class="badge-upcoming">Upcoming</span>
                        @endif
                    </div>
                    <div class="meeting-card-meta">
                        @if($meeting->start_at)
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $meeting->start_at->format('M d, Y · g:i A') }}
                            </span>
                            <span class="muted-small">{{ $meeting->start_at->diffForHumans() }}</span>
                        @else
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Instant meeting
                            </span>
                        @endif
                        @if($meeting->organization && $meeting->organization->name)
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $meeting->organization->name }}
                            </span>
                        @endif
                        @if(auth()->user()?->hasRole('org-admin') || auth()->user()?->hasRole('super-admin'))
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                By {{ $meeting->creator?->name ?? 'Unknown' }}
                            </span>
                        @endif
                    </div>
                    <div class="meeting-card-footer">
                        @php($activeParticipants = (int) $meeting->active_participant_count)
                        <span class="muted-small">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="meeting-inline-icon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $activeParticipants }} participant{{ $activeParticipants !== 1 ? 's' : '' }}
                        </span>
                        <div class="meeting-card-actions">
                            <a href="{{ route('dashboard.meetings.summary', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Summary">Summary</a>
                            <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Diagnostics">Diagnostics</a>
                            <button type="button" class="btn btn-sm btn-ghost copy-btn" title="Copy meeting link" onclick="copyMeetingLink(this, '{{ route('meeting.show', $meeting->id) }}')">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Copy Link
                                <span class="copied-tip">Copied!</span>
                            </button>
                            <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm {{ $isLive ? 'btn-primary' : 'btn-secondary' }}" target="_blank">
                                {{ $isLive ? 'Join Now' : 'View' }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Past Meetings</h3>
        <span class="section-count">{{ $pastMeetings->count() }}</span>
    </div>
    <div class="card-body no-padding">
        @if($pastMeetings->isEmpty())
            <div class="empty-meetings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>No past meetings yet.</p>
            </div>
        @else
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th class="table-cell-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastMeetings as $meeting)
                        <tr>
                            <td>
                                <strong>{{ $meeting->title }}</strong>
                                @if($meeting->description)
                                    <br><small class="muted-small">{{ Str::limit($meeting->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($meeting->start_at)
                                    {{ $meeting->start_at->format('M d, Y g:i A') }}<br>
                                    <small class="muted-small">{{ $meeting->start_at->diffForHumans() }}</small>
                                @else
                                    <small class="muted-small">Instant</small>
                                @endif
                            </td>
                            <td>{{ $meeting->active_participant_count }}</td>
                            <td>
                                <span class="badge-ended">Ended</span>
                                @if(auth()->user()?->hasRole('org-admin') || auth()->user()?->hasRole('super-admin'))
                                    <br><small class="muted-small">By {{ $meeting->creator?->name ?? 'Unknown' }}</small>
                                @endif
                            </td>
                            <td class="table-cell-right">
                                <a href="{{ route('dashboard.meetings.summary', $meeting->id) }}" class="btn btn-sm btn-ghost">Summary</a>
                                <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost">Diagnostics</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
function copyMeetingLink(btn, url) {
    navigator.clipboard.writeText(url).then(function() {
        btn.classList.add('did-copy');
        setTimeout(() => btn.classList.remove('did-copy'), 1800);
    }).catch(function() {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        btn.classList.add('did-copy');
        setTimeout(() => btn.classList.remove('did-copy'), 1800);
    });
}
</script>
@endsection
