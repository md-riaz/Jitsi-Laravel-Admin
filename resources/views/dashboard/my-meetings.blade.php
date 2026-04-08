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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
    gap: 0.75rem;
    transition: box-shadow 0.15s, border-color 0.15s;
    min-width: 0;
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
    gap: 0.75rem;
}

.meeting-card-title-wrap {
    flex: 1;
    min-width: 0;
}

.meeting-card-badges {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.meeting-card-title {
    font-weight: 600;
    font-size: 0.9375rem;
    color: var(--foreground);
    line-height: 1.3;
    word-break: break-word;
}

.meeting-card-desc {
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    margin-top: 2px;
    word-break: break-word;
}

.meeting-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.625rem;
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    align-items: center;
}

.meeting-card-meta span {
    min-width: 0;
}

.meeting-card-meta svg {
    width: 13px;
    height: 13px;
    display: inline;
    vertical-align: middle;
    margin-right: 3px;
}

.meeting-card-footer {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: space-between;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border);
    gap: 0.75rem;
}

.meeting-org-name {
    color: var(--muted-foreground);
    font-size: 0.875rem;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.meeting-card-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.meeting-card-actions .btn,
.meeting-card-actions .delete-inline-form {
    flex: 1 1 auto;
}

.meeting-card-actions .btn,
.meeting-card-actions .delete-inline-form button {
    width: 100%;
    justify-content: center;
}

.badge-live,
.badge-upcoming,
.badge-ended,
.badge-instant {
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
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
    flex-wrap: wrap;
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

.delete-inline-form {
    display: inline-flex;
}

.btn-delete-disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.section-pagination {
    margin-top: 1rem;
}

.section-pagination nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.section-pagination nav > div:first-child {
    color: var(--muted-foreground);
    font-size: 0.875rem;
}

.section-pagination nav > div:last-child {
    margin-left: auto;
}

.section-pagination nav > div:last-child > span,
.section-pagination nav > div:last-child > a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.section-pagination svg {
    width: 16px;
    height: 16px;
}

@media (min-width: 768px) {
    .meeting-card-footer {
        flex-direction: row;
        align-items: flex-start;
    }

    .meeting-org-name {
        max-width: 34%;
    }

    .meeting-card-actions {
        justify-content: flex-end;
    }

    .meeting-card-actions .btn,
    .meeting-card-actions .delete-inline-form {
        flex: 0 1 auto;
    }

    .meeting-card-actions .btn,
    .meeting-card-actions .delete-inline-form button {
        width: auto;
    }
}

@media (max-width: 640px) {
    .meeting-cards-grid {
        grid-template-columns: 1fr;
    }

    .meeting-card {
        padding: 1rem;
    }

    .meeting-card-top {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Meetings</h1>
            <p class="page-description">Manage your live, upcoming, and past meetings.</p>
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
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Upcoming Meetings</div><div class="meeting-stat-value">{{ $analytics['upcoming_meetings'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div class="meeting-stat-label">Past Meetings</div><div class="meeting-stat-value">{{ $analytics['past_meetings'] ?? 0 }}</div></div></div>
</div>

@if($liveMeetings->isNotEmpty())
<div class="card card-spaced" style="border: 2px solid color-mix(in srgb, var(--success), transparent 70%);">
    <div class="card-header" style="border-bottom: 1px solid color-mix(in srgb, var(--success), transparent 82%); background: color-mix(in srgb, var(--success), white 94%);">
        <h3 class="card-title" style="color: color-mix(in srgb, var(--success), black 22%);">Live Meetings</h3>
        <span class="section-count">{{ $liveMeetings->count() }}</span>
    </div>
    <div class="card-body">
        <div class="meeting-cards-grid">
            @foreach($liveMeetings as $meeting)
            <div class="meeting-card live">
                <div class="meeting-card-top">
                    <div class="meeting-card-title-wrap">
                        <div class="meeting-card-title">{{ $meeting->title }}</div>
                        @if($meeting->description)
                            <div class="meeting-card-desc">{{ Str::limit($meeting->description, 70) }}</div>
                        @endif
                    </div>
                    <div class="meeting-card-badges">
                        @if($meeting->organization && $meeting->organization->name)
                            <span class="badge-instant">{{ $meeting->organization->name }}</span>
                        @endif
                        <span class="badge-live">Live</span>
                    </div>
                </div>
                <div class="meeting-card-meta">
                    <span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ $meeting->actual_started_at ? 'Started ' . $meeting->actual_started_at->diffForHumans() : ($meeting->isInstantMeeting() ? 'Instant meeting' : 'Started ' . optional($meeting->start_at)->diffForHumans()) }}
                    </span>
                </div>
                <div class="meeting-card-footer">
                    <div class="meeting-card-actions">
                        @can('deleteVisible', $meeting)
                            <button type="button" class="btn btn-sm btn-danger btn-delete-disabled" title="Live meetings cannot be deleted while ongoing" disabled>Delete</button>
                        @endcan
                        <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost">Diagnostics</a>
                        <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-primary" target="_blank">Join Now</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
    </div>
</div>
@endif

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
                <div class="meeting-card">
                    <div class="meeting-card-top">
                        <div class="meeting-card-title-wrap">
                            <div class="meeting-card-title">{{ $meeting->title }}</div>
                            @if($meeting->description)
                                <div class="meeting-card-desc">{{ Str::limit($meeting->description, 70) }}</div>
                            @endif
                        </div>
                        @if($meeting->isInstantMeeting())
                            <span class="badge-instant">Instant</span>
                        @else
                            <span class="badge-upcoming">Upcoming</span>
                        @endif
                    </div>
                    <div class="meeting-card-meta">
                        <span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $meeting->organization && $meeting->organization->name ? $meeting->organization->name : 'No organization' }}
                        </span>
                        @if(auth()->user()?->hasRole('org-admin') || auth()->user()?->hasRole('super-admin'))
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                By {{ $meeting->creator?->name ?? 'Unknown' }}
                            </span>
                        @endif
                        @if($meeting->start_at)
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $meeting->start_at->format('M d, Y · g:i A') }}
                            </span>
                        @else
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Instant meeting
                            </span>
                        @endif
                    </div>
                    <div class="meeting-card-footer">
                        <div class="meeting-card-actions">
                            @can('update', $meeting)
                                <a href="{{ route('dashboard.meetings.edit', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Edit">Edit</a>
                            @endcan
                            @can('delete', $meeting)
                                <form method="POST" action="{{ route('dashboard.meetings.destroy', $meeting->id) }}" class="delete-inline-form" onsubmit="return confirm('Delete this meeting? This will remove it from dashboards and disable join links.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">Delete</button>
                                </form>
                            @endcan
                                                        <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Diagnostics">Diagnostics</a>
                            <button type="button" class="btn btn-sm btn-ghost copy-btn" title="Copy meeting link" onclick="copyMeetingLink(this, '{{ route('meeting.show', $meeting->id) }}')">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Copy Link
                                <span class="copied-tip">Copied!</span>
                            </button>
                            <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-secondary" target="_blank">View</a>
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
        <span class="section-count">{{ $pastMeetings->total() }}</span>
    </div>
    <div class="card-body">
        @if($pastMeetings->isEmpty())
            <div class="empty-meetings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>No past meetings yet.</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pastMeetings as $meeting)
                        <tr>
                            <td>{{ $meeting->title }}</td>
                            <td>{{ $meeting->description }}</td>
                            <td>{{ optional($meeting->start_at)->format('M d, Y · g:i A') }}</td>
                            <td>{{ optional($meeting->end_at)->format('M d, Y · g:i A') }}</td>
                            <td>{{ $meeting->creator?->name ?? 'N/A' }}</td>
                            <td>
                                <div class="meeting-card-actions">
                                    @can('update', $meeting)
                                        <a href="{{ route('dashboard.meetings.edit', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Edit">Edit</a>
                                    @endcan
                                    @can('delete', $meeting)
                                        <form method="POST" action="{{ route('dashboard.meetings.destroy', $meeting->id) }}" class="delete-inline-form" onsubmit="return confirm('Delete this meeting? This will remove it from dashboards and disable join links.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">Delete</button>
                                        </form>
                                    @endcan
                                    <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Diagnostics">Diagnostics</a>
                                    <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-secondary" target="_blank">View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($pastMeetings->hasPages())
                <div class="section-pagination">{{ $pastMeetings->links() }}</div>
            @endif
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
