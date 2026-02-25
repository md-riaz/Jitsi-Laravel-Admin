@extends('tyro-dashboard::layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Welcome back, {{ $user->name ?? 'User' }}!</h1>
            <p class="page-description">Here's what's happening with your account today.</p>
        </div>
        @if($isAdmin ?? false)
        <div style="display: flex; gap: 10px;">
            <form method="POST" action="{{ route('dashboard.create-meeting.store') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="meeting_type" value="instant">
                <input type="hidden" name="title" value="Instant Meeting">
                <input type="hidden" name="timezone" value="UTC">
                <input type="hidden" name="visibility" value="invite_only">
                <button type="submit" class="btn btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    New Meeting
                </button>
            </form>
            <a href="{{ route('dashboard.create-meeting') }}?type=scheduled" class="btn btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Schedule
            </a>
        </div>
        @endif
    </div>
</div>

@if($isAdmin ?? false)
<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Roles</div>
            <div class="stat-value">{{ number_format($stats['total_roles'] ?? 0) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Privileges</div>
            <div class="stat-value">{{ number_format($stats['total_privileges'] ?? 0) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label">Suspended Users</div>
            <div class="stat-value">{{ number_format($stats['suspended_users'] ?? 0) }}</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Users</h3>
            <a href="{{ route('tyro-dashboard.users.index') }}" class="btn btn-sm btn-ghost">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($stats['recent_users']) && $stats['recent_users']->count())
            <div class="table-container">
                <table class="table">
                    <tbody>
                        @foreach($stats['recent_users'] as $recentUser)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar" style="{{ ($recentUser->profile_photo_path || $recentUser->use_gravatar) ? 'background: none; padding: 0;' : '' }}">
                                        @if($recentUser->profile_photo_path || ($recentUser->use_gravatar && $recentUser->email))
                                            <img src="{{ $recentUser->profile_photo_url }}" alt="{{ $recentUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        @else
                                            {{ strtoupper(substr($recentUser->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="user-cell-info">
                                        <div class="user-cell-name">{{ $recentUser->name }}</div>
                                        <div class="user-cell-email">{{ $recentUser->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                @if(method_exists($recentUser, 'isSuspended') && $recentUser->isSuspended())
                                    <span class="badge badge-danger">Suspended</span>
                                @else
                                    <span class="badge badge-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p class="empty-state-description">No users found.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Role Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Role Distribution</h3>
            <a href="{{ route('tyro-dashboard.roles.index') }}" class="btn btn-sm btn-ghost">Manage Roles</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if(isset($stats['role_distribution']) && $stats['role_distribution']->count())
            <div class="table-container">
                <table class="table">
                    <tbody>
                        @foreach($stats['role_distribution'] as $roleStat)
                        <tr>
                            <td>
                                <span class="badge badge-primary">{{ $roleStat['name'] }}</span>
                            </td>
                            <td style="text-align: right;">
                                <strong>{{ $roleStat['count'] }}</strong> users
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p class="empty-state-description">No roles found.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@else
<!-- User Dashboard (Non-Admin) - Meeting-Focused Home -->
<style>
.meeting-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 28px 32px;
    color: white;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.meeting-hero-text h2 { font-size: 1.375rem; font-weight: 700; margin: 0 0 5px 0; }
.meeting-hero-text p { font-size: 0.9375rem; opacity: 0.88; margin: 0; }
.meeting-quick-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.btn-new-meeting {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: white; color: #667eea; border: none; border-radius: 8px;
    font-size: 0.9375rem; font-weight: 600; cursor: pointer; text-decoration: none;
    transition: box-shadow 0.15s, transform 0.1s; white-space: nowrap;
}
.btn-new-meeting:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.18); transform: translateY(-1px); color: #5a67d8; text-decoration: none; }
.btn-schedule-meeting {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: rgba(255,255,255,0.15); color: white; border: 1.5px solid rgba(255,255,255,0.5);
    border-radius: 8px; font-size: 0.9375rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: background 0.15s, transform 0.1s; white-space: nowrap;
}
.btn-schedule-meeting:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); color: white; text-decoration: none; }
.btn-join-meeting {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: rgba(255,255,255,0.1); color: white; border: 1.5px solid rgba(255,255,255,0.35);
    border-radius: 8px; font-size: 0.9375rem; font-weight: 600; cursor: pointer;
    text-decoration: none; transition: background 0.15s; white-space: nowrap;
}
.btn-join-meeting:hover { background: rgba(255,255,255,0.2); color: white; text-decoration: none; }
.meeting-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 22px; }
.meeting-stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 16px 18px; display: flex; align-items: center; gap: 12px; }
.meeting-stat-icon { width: 40px; height: 40px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.meeting-stat-icon svg { width: 19px; height: 19px; }
.meeting-stat-icon.icon-blue { background: #eff6ff; color: #3b82f6; }
.meeting-stat-icon.icon-green { background: #f0fdf4; color: #22c55e; }
.meeting-stat-icon.icon-purple { background: #faf5ff; color: #a855f7; }
.meeting-stat-icon.icon-orange { background: #fff7ed; color: #f97316; }
.meeting-stat-label { font-size: 0.8125rem; color: var(--muted-foreground); margin-bottom: 2px; }
.meeting-stat-value { font-size: 1.25rem; font-weight: 700; color: var(--foreground); line-height: 1; }
.ml-list-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
.ml-list-item:last-child { border-bottom: none; }
.ml-time { min-width: 85px; text-align: right; font-size: 0.8125rem; color: var(--muted-foreground); flex-shrink: 0; }
.ml-time .t-main { font-size: 0.875rem; font-weight: 600; color: var(--foreground); display: block; }
.ml-info { flex: 1; min-width: 0; }
.ml-title { font-weight: 600; font-size: 0.9375rem; color: var(--foreground); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ml-sub { font-size: 0.8125rem; color: var(--muted-foreground); margin-top: 2px; }
.ml-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
.badge-live-dot { background: #dcfce7; color: #16a34a; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-live-dot::before { content: ''; width: 6px; height: 6px; background: #16a34a; border-radius: 50%; display: inline-block; animation: pdot 1.5s infinite; }
@keyframes pdot { 0%,100%{opacity:1}50%{opacity:0.4} }
.badge-sch { background: #fef9c3; color: #854d0e; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.empty-mt { text-align: center; padding: 36px 20px; color: var(--muted-foreground); }
.empty-mt svg { width: 44px; height: 44px; margin: 0 auto 10px; display: block; opacity: 0.35; }
.empty-mt p { margin: 0 0 14px 0; font-size: 0.9375rem; }
/* Join Modal */
.jm-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.jm-overlay.open { display: flex; }
.jm-box { background: var(--card); border-radius: 12px; padding: 26px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.jm-box h3 { margin: 0 0 5px 0; font-size: 1.125rem; font-weight: 700; }
.jm-box p { margin: 0 0 18px 0; font-size: 0.875rem; color: var(--muted-foreground); }
.jm-box input { width: 100%; padding: 10px 13px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 0.9375rem; background: var(--background); color: var(--foreground); box-sizing: border-box; margin-bottom: 14px; }
.jm-box input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
.jm-footer { display: flex; gap: 10px; justify-content: flex-end; }
</style>

@php
    $dashUser = auth()->user();
    $dashMeetings = \App\Models\Meeting::where(function($q) use ($dashUser) {
        $q->where('created_by', $dashUser->id)
          ->orWhereHas('participants', fn($p) => $p->where('user_id', $dashUser->id));
    })->with(['organization', 'participants'])->get();

    $dashLive = $dashMeetings->filter(fn($m) => $m->canJoinAt(now()));

    $dashUpcoming = \App\Models\Meeting::where(function($q) use ($dashUser) {
        $q->where('created_by', $dashUser->id)
          ->orWhereHas('participants', fn($p) => $p->where('user_id', $dashUser->id));
    })->where(function($q) {
        $q->whereNull('end_at')->orWhere('end_at', '>', now());
    })->where('status', '!=', 'live')->orderBy('start_at')->with(['organization', 'participants'])->limit(5)->get();
@endphp

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
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                New Meeting
            </button>
        </form>
        <a href="{{ route('dashboard.create-meeting') }}?type=scheduled" class="btn-schedule-meeting">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Schedule
        </a>
        <button type="button" class="btn-join-meeting" onclick="document.getElementById('jmModal').classList.add('open')">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Join
        </button>
    </div>
</div>

<!-- Stats -->
<div class="meeting-stats-row">
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-blue">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="meeting-stat-label">Upcoming</div>
            <div class="meeting-stat-value">{{ $dashUpcoming->count() }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-green">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="meeting-stat-label">Live Now</div>
            <div class="meeting-stat-value">{{ $dashLive->count() }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-purple">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
            <div class="meeting-stat-label">Total Hosted</div>
            <div class="meeting-stat-value">{{ $dashMeetings->where('created_by', auth()->id())->count() }}</div>
        </div>
    </div>
    <div class="meeting-stat-card">
        <div class="meeting-stat-icon icon-orange">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <div class="meeting-stat-label">Participants</div>
            <div class="meeting-stat-value">{{ $dashMeetings->sum(fn($m) => $m->participants->count()) }}</div>
        </div>
    </div>
</div>

@if($dashLive->count())
<div class="card" style="margin-bottom: 18px; border: 2px solid #22c55e;">
    <div class="card-header" style="border-bottom: 1px solid #dcfce7; background: #f0fdf4;">
        <h3 class="card-title" style="color: #16a34a; display: flex; align-items: center; gap: 8px;">
            <span style="width:9px;height:9px;background:#16a34a;border-radius:50%;display:inline-block;animation:pdot 1.5s infinite;"></span>
            Live Meetings
        </h3>
    </div>
    <div class="card-body" style="padding: 0 20px;">
        @foreach($dashLive as $m)
        <div class="ml-list-item">
            <div class="ml-info">
                <div class="ml-title">{{ $m->title }}</div>
                <div class="ml-sub">{{ $m->isInstantMeeting() ? 'Instant meeting' : 'Started ' . $m->start_at->diffForHumans() }} · {{ $m->participants->count() }} in room</div>
            </div>
            <div class="ml-actions">
                <span class="badge-live-dot">Live</span>
                <a href="{{ route('meeting.show', $m->id) }}" class="btn btn-sm btn-primary" target="_blank">Join Now</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Upcoming Meetings</h3>
        <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-sm btn-ghost">View All</a>
    </div>
    <div class="card-body" style="padding: 0 20px;">
        @if($dashUpcoming->isEmpty())
            <div class="empty-mt">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                <p>No upcoming meetings.</p>
                <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary btn-sm">Schedule a Meeting</a>
            </div>
        @else
            @foreach($dashUpcoming as $m)
            @php $isLive = $m->canJoinAt(now()); @endphp
            <div class="ml-list-item">
                @if($m->start_at)
                <div class="ml-time">
                    <span class="t-main">{{ $m->start_at->format('g:i A') }}</span>
                    {{ $m->start_at->format('M d') }}
                </div>
                @else
                <div class="ml-time"><span class="t-main">Now</span>Instant</div>
                @endif
                <div class="ml-info">
                    <div class="ml-title">{{ $m->title }}</div>
                    <div class="ml-sub">
                        @if($m->start_at) {{ $m->start_at->diffForHumans() }} @endif
                        @if($m->organization && $m->organization->name) · {{ $m->organization->name }} @endif
                    </div>
                </div>
                <div class="ml-actions">
                    @if($isLive)
                        <span class="badge-live-dot">Live</span>
                        <a href="{{ route('meeting.show', $m->id) }}" class="btn btn-sm btn-primary" target="_blank">Join</a>
                    @else
                        <span class="badge-sch">Upcoming</span>
                        <a href="{{ route('meeting.show', $m->id) }}" class="btn btn-sm btn-ghost" target="_blank">Details</a>
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Join Meeting Modal -->
<div id="jmModal" class="jm-overlay" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="jm-box">
        <h3>Join a Meeting</h3>
        <p>Enter the meeting link or ID to join.</p>
        <input type="text" id="jmInput" placeholder="Paste meeting link or ID…">
        <div class="jm-footer">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('jmModal').classList.remove('open')">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="doJoin()">Join</button>
        </div>
    </div>
</div>
<script>
function doJoin() {
    const v = document.getElementById('jmInput').value.trim();
    if (!v) return;
    window.open(v.startsWith('http') ? v : '/meet/' + encodeURIComponent(v), '_blank');
    document.getElementById('jmModal').classList.remove('open');
    document.getElementById('jmInput').value = '';
}
document.getElementById('jmInput').addEventListener('keydown', e => { if(e.key==='Enter') doJoin(); });
</script>

@endif
@endsection
