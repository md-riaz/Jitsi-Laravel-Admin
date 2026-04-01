@extends('tyro-dashboard::layouts.user')

@section('title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
<!-- Meeting-Focused Home -->
<style>
.meeting-hero {
    background: #1d4ed8;
    border-radius: 12px;
    padding: 32px;
    color: white;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.meeting-hero-text h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 6px 0;
}
.meeting-hero-text p {
    font-size: 0.9375rem;
    opacity: 0.88;
    margin: 0;
}
.meeting-quick-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.btn-new-meeting {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: box-shadow 0.15s, transform 0.1s;
    white-space: nowrap;
}
.btn-new-meeting:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    transform: translateY(-1px);
    color: #5a67d8;
    text-decoration: none;
}
.btn-schedule-meeting {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1.5px solid rgba(255,255,255,0.5);
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, transform 0.1s;
    white-space: nowrap;
}
.btn-schedule-meeting:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}
.btn-join-meeting {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: rgba(255,255,255,0.1);
    color: white;
    border: 1.5px solid rgba(255,255,255,0.35);
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, transform 0.1s;
    white-space: nowrap;
}
.btn-join-meeting:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}
.meeting-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.meeting-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.meeting-stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.meeting-stat-icon svg { width: 20px; height: 20px; }
.meeting-stat-icon.icon-blue { background: #eff6ff; color: #3b82f6; }
.meeting-stat-icon.icon-green { background: #f0fdf4; color: #22c55e; }
.meeting-stat-icon.icon-purple { background: #faf5ff; color: #a855f7; }
.meeting-stat-icon.icon-orange { background: #fff7ed; color: #f97316; }
.meeting-stat-label { font-size: 0.8125rem; color: var(--muted-foreground); margin-bottom: 2px; }
.meeting-stat-value { font-size: 1.375rem; font-weight: 700; color: var(--foreground); line-height: 1; }
.meeting-card-list { display: flex; flex-direction: column; gap: 0; }
.meeting-list-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
}
.meeting-list-item:last-child { border-bottom: none; }
.meeting-list-time {
    min-width: 90px;
    text-align: right;
    font-size: 0.8125rem;
    color: var(--muted-foreground);
    flex-shrink: 0;
}
.meeting-list-time .time-main { font-size: 0.875rem; font-weight: 600; color: var(--foreground); display: block; }
.meeting-list-info { flex: 1; min-width: 0; }
.meeting-list-title { font-weight: 600; font-size: 0.9375rem; color: var(--foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.meeting-list-sub { font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 2px; }
.meeting-list-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
.badge-live { background: #dcfce7; color: #16a34a; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-live::before { content: ''; width: 6px; height: 6px; background: #16a34a; border-radius: 50%; display: inline-block; animation: pulse-dot 1.5s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.4} }
.badge-upcoming { background: #fef9c3; color: #854d0e; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-ended { background: #f1f5f9; color: #64748b; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.empty-meetings { text-align: center; padding: 40px 20px; color: var(--muted-foreground); }
.empty-meetings svg { width: 48px; height: 48px; margin: 0 auto 12px; display: block; opacity: 0.4; }
.empty-meetings p { margin: 0 0 16px 0; font-size: 0.9375rem; }

/* Join Meeting Modal */
.join-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.join-modal-overlay.open { display: flex; }
.join-modal {
    background: var(--card);
    border-radius: 12px;
    padding: 28px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.join-modal h3 { margin: 0 0 6px 0; font-size: 1.125rem; font-weight: 700; }
.join-modal p { margin: 0 0 20px 0; font-size: 0.875rem; color: var(--muted-foreground); }
.join-modal input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 0.9375rem;
    background: var(--background);
    color: var(--foreground);
    box-sizing: border-box;
    margin-bottom: 16px;
}
.join-modal input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
.join-modal-footer { display: flex; gap: 10px; justify-content: flex-end; }
</style>

<!-- Hero: Quick Actions -->
<div class="meeting-hero">
    <div class="meeting-hero-text">
        <h2>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $user->name ?? 'User')[0] }}!</h2>
        <p>Start or schedule a meeting in seconds.</p>
    </div>
    <div class="meeting-quick-actions">
        <form method="POST" action="{{ route('dashboard.create-meeting.store') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="meeting_type" value="instant">
            <input type="hidden" name="title" value="Instant Meeting">
            <input type="hidden" name="timezone" value="UTC">
            <input type="hidden" name="visibility" value="invite_only">
            <button type="submit" class="btn-new-meeting">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                New Meeting
            </button>
        </form>
        <a href="{{ route('dashboard.create-meeting') }}?type=scheduled" class="btn-schedule-meeting">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Schedule
        </a>
        <button type="button" class="btn-join-meeting" onclick="document.getElementById('joinModal').classList.add('open')">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Join
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="meeting-stats-row">
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-blue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div>
            <div class="meeting-stat-label">Upcoming</div>
            <div class="meeting-stat-value">{{ $upcomingMeetings->count() }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-green">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </div>
        <div>
            <div class="meeting-stat-label">Live Now</div>
            <div class="meeting-stat-value">{{ $liveMeetings->count() }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-purple">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div>
            <div class="meeting-stat-label">Total Hosted</div>
            <div class="meeting-stat-value">{{ $totalMeetings }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-orange">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <div>
            <div class="meeting-stat-label">Participants</div>
            <div class="meeting-stat-value">{{ $totalParticipants }}</div>
        </div>
    </div>
</div>

<!-- Live Meetings (if any) -->
@if($liveMeetings->count())
<div class="card" style="margin-bottom: 20px; border: 2px solid #22c55e;">
    <div class="card-header" style="border-bottom: 1px solid #dcfce7; background: #f0fdf4;">
        <h3 class="card-title" style="color: #16a34a; display: flex; align-items: center; gap: 8px;">
            <span style="width:10px;height:10px;background:#16a34a;border-radius:50%;display:inline-block;animation:pulse-dot 1.5s infinite;"></span>
            Live Meetings
        </h3>
    </div>
    <div class="card-body" style="padding: 0 20px;">
        <div class="meeting-card-list">
            @foreach($liveMeetings as $meeting)
            <div class="meeting-list-item">
                <div class="meeting-list-info">
                    <div class="meeting-list-title">{{ $meeting->title }}</div>
                    <div class="meeting-list-sub">
                        {{ $meeting->isInstantMeeting() ? 'Instant meeting' : 'Started ' . optional($meeting->start_at)->diffForHumans() }}
                        · {{ $meeting->participants->where('left_at', null)->count() ?: $meeting->participants->count() }} in room
                    </div>
                </div>
                <div class="meeting-list-actions">
                    <span class="badge-live">Live</span>
                    <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-primary" target="_blank">Join Now</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Upcoming Meetings -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Upcoming Meetings</h3>
        <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-sm btn-ghost">View All</a>
    </div>
    <div class="card-body" style="padding: 0 20px;">
        @if($upcomingMeetings->isEmpty())
            <div class="empty-meetings">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <p>No upcoming meetings.</p>
                <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary btn-sm">Schedule a Meeting</a>
            </div>
        @else
            <div class="meeting-card-list">
                @foreach($upcomingMeetings->take(5) as $meeting)
                <div class="meeting-list-item">
                    @if($meeting->start_at)
                    <div class="meeting-list-time">
                        <span class="time-main">{{ $meeting->start_at->format('g:i A') }}</span>
                        {{ $meeting->start_at->format('M d') }}
                    </div>
                    @else
                    <div class="meeting-list-time">
                        <span class="time-main">Now</span>
                        Instant
                    </div>
                    @endif
                    <div class="meeting-list-info">
                        <div class="meeting-list-title">{{ $meeting->title }}</div>
                        <div class="meeting-list-sub">
                            @if($meeting->start_at)
                                {{ $meeting->start_at->diffForHumans() }}
                            @endif
                            @if($meeting->organization && $meeting->organization->name)
                                · {{ $meeting->organization->name }}
                            @endif
                        </div>
                    </div>
                    <div class="meeting-list-actions">
                        @if($meeting->canJoinAt(now()))
                            <span class="badge-live">Live</span>
                            <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-primary" target="_blank">Join</a>
                        @else
                            <span class="badge-upcoming">Upcoming</span>
                            <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-ghost" target="_blank">Details</a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Join Meeting Modal -->
<div id="joinModal" class="join-modal-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="join-modal">
        <h3>Join a Meeting</h3>
        <p>Enter the meeting link or meeting ID to join.</p>
        <input type="text" id="joinMeetingInput" placeholder="Paste meeting link or ID…" autofocus>
        <div class="join-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('joinModal').classList.remove('open')">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="joinMeeting()">Join</button>
        </div>
    </div>
</div>

<script>
function joinMeeting() {
    const input = document.getElementById('joinMeetingInput').value.trim();
    if (!input) return;
    // Accept full URL or just an ID/slug
    let url = input;
    if (!input.startsWith('http')) {
        // Treat as meeting ID – navigate to the standard meet route
        url = '/meet/' + encodeURIComponent(input);
    }
    window.open(url, '_blank');
    document.getElementById('joinModal').classList.remove('open');
    document.getElementById('joinMeetingInput').value = '';
}
document.getElementById('joinMeetingInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') joinMeeting();
});
</script>
@endsection
