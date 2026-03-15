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
    gap: 16px;
    margin-bottom: 4px;
}
.meeting-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: box-shadow 0.15s, border-color 0.15s;
}
.meeting-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.09); border-color: #667eea40; }
.meeting-card.live { border-left: 3px solid #22c55e; }
.meeting-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.meeting-card-title { font-weight: 600; font-size: 0.9375rem; color: var(--foreground); line-height: 1.3; }
.meeting-card-desc { font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 2px; }
.meeting-card-meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: 0.8125rem; color: var(--muted-foreground); align-items: center; }
.meeting-card-meta svg { width: 13px; height: 13px; display: inline; vertical-align: middle; margin-right: 3px; }
.meeting-card-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid var(--border); }
.badge-live { background: #dcfce7; color: #16a34a; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-live::before { content: ''; width: 6px; height: 6px; background: #16a34a; border-radius: 50%; display: inline-block; animation: pulse-dot 1.5s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.4} }
.badge-upcoming { background: #fef9c3; color: #854d0e; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-ended { background: #f1f5f9; color: #64748b; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-instant { background: #eff6ff; color: #3b82f6; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.empty-meetings { text-align: center; padding: 48px 20px; color: var(--muted-foreground); }
.empty-meetings svg { width: 52px; height: 52px; margin: 0 auto 14px; display: block; opacity: 0.35; }
.empty-meetings p { margin: 0 0 16px 0; font-size: 0.9375rem; }

/* Copy link tooltip */
.copy-btn { position: relative; }
.copy-btn .copied-tip {
    position: absolute; bottom: 110%; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: white; font-size: 0.75rem; padding: 3px 8px;
    border-radius: 4px; white-space: nowrap; opacity: 0; pointer-events: none;
    transition: opacity 0.15s;
}
.copy-btn.did-copy .copied-tip { opacity: 1; }
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Meetings</h1>
            <p class="page-description" style="font-size: 1rem;">Manage your upcoming and past meetings.</p>
        </div>
        <div style="display: flex; gap: 10px;">
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


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;">
    <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Total Meetings</div><div style="font-size:24px;font-weight:700;">{{ $analytics['total_meetings'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Live Now</div><div style="font-size:24px;font-weight:700;">{{ $analytics['live_now'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Avg Participants</div><div style="font-size:24px;font-weight:700;">{{ $analytics['avg_participants'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Avg Duration (min)</div><div style="font-size:24px;font-weight:700;">{{ $analytics['avg_duration_minutes'] ?? 0 }}</div></div></div>
    <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Join Events (30d)</div><div style="font-size:24px;font-weight:700;">{{ $analytics['join_events_30d'] ?? 0 }}</div></div></div>
</div>

<!-- Upcoming Meetings -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title">Upcoming Meetings</h3>
        <span style="font-size: 0.8125rem; color: var(--muted-foreground); background: var(--muted); padding: 2px 10px; border-radius: 20px;">{{ $upcomingMeetings->count() }}</span>
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
                            <span style="color: #9ca3af;">{{ $meeting->start_at->diffForHumans() }}</span>
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
                    </div>
                    <div class="meeting-card-footer">
                        <span style="font-size: 0.8125rem; color: var(--muted-foreground);">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $meeting->participants->count() }} participant{{ $meeting->participants->count() !== 1 ? 's' : '' }}
                        </span>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                            <a href="{{ route('dashboard.meetings.summary', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Summary">
                                Summary
                            </a>
                            <a href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}" class="btn btn-sm btn-ghost" title="Diagnostics">
                                Diagnostics
                            </a>
                            <button type="button" class="btn btn-sm btn-ghost copy-btn" title="Copy meeting link"
                                onclick="copyMeetingLink(this, '{{ route('meeting.show', $meeting->id) }}')">
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

<!-- Past Meetings -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Past Meetings</h3>
        <span style="font-size: 0.8125rem; color: var(--muted-foreground); background: var(--muted); padding: 2px 10px; border-radius: 20px;">{{ $pastMeetings->count() }}</span>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($pastMeetings->isEmpty())
            <div class="empty-meetings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>No past meetings yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date</th>
                            <th>Participants</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastMeetings as $meeting)
                        <tr>
                            <td>
                                <strong>{{ $meeting->title }}</strong>
                                @if($meeting->description)
                                    <br><small style="color: #6b7280;">{{ Str::limit($meeting->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($meeting->start_at)
                                    {{ $meeting->start_at->format('M d, Y g:i A') }}<br>
                                    <small style="color: #6b7280;">{{ $meeting->start_at->diffForHumans() }}</small>
                                @else
                                    <small style="color: #6b7280;">Instant</small>
                                @endif
                            </td>
                            <td>{{ $meeting->participants->count() }}</td>
                            <td><span class="badge-ended">Ended</span></td>
                            <td style="text-align:right;">
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
        // Fallback for environments where clipboard API is unavailable
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

