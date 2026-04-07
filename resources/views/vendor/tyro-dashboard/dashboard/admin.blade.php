@extends('tyro-dashboard::layouts.admin')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
<span>Dashboard</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Welcome back, {{ $user->name ?? 'User' }}!</h1>
            <p class="page-description" style="font-size: 1rem;">Here's what's happening with your application today.</p>
        </div>
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
            <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-ghost">
                My Meetings
            </a>
        </div>
    </div>
</div>

@php($isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin'))
@php($isOrgAdmin = method_exists($user, 'hasRole') && $user->hasRole('org-admin') && ! $isSuperAdmin)

@if($isSuperAdmin)
<!-- Stats Grid -->
<div class="stats-grid">
    <a href="{{ route('tyro-dashboard.users.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Total Users</div>
            <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
        </div>
    </a>

    <a href="{{ route('tyro-dashboard.roles.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Total Roles</div>
            <div class="stat-value">{{ number_format($stats['total_roles'] ?? 0) }}</div>
        </div>
    </a>

    <a href="{{ route('tyro-dashboard.privileges.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Total Privileges</div>
            <div class="stat-value">{{ number_format($stats['total_privileges'] ?? 0) }}</div>
        </div>
    </a>

    <a href="{{ route('tyro-dashboard.users.index', ['status' => 'suspended']) }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Suspended Users</div>
            <div class="stat-value">{{ number_format($stats['suspended_users'] ?? 0) }}</div>
        </div>
    </a>
</div>

<div class="grid-2">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Recent Users</h3>
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
                                <a href="{{ route('tyro-dashboard.users.edit', $recentUser->id) }}" class="user-cell" style="text-decoration: none;">
                                    <div class="user-cell-avatar" style="{{ ($recentUser->profile_photo_path || $recentUser->use_gravatar) ? 'background: none; padding: 0;' : '' }}">
                                        @if($recentUser->profile_photo_path || ($recentUser->use_gravatar && $recentUser->email))
                                            <img src="{{ $recentUser->profile_photo_url }}" alt="{{ $recentUser->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                        @else
                                            {{ strtoupper(substr($recentUser->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="user-cell-info">
                                        <div class="user-cell-name" style="font-size: 0.9375rem;">{{ $recentUser->name }}</div>
                                        <div class="user-cell-email" style="font-size: 0.8125rem;">{{ $recentUser->email }}</div>
                                    </div>
                                </a>
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
                <p class="empty-state-description" style="font-size: 0.9375rem;">No users found.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Role Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Role Distribution</h3>
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
                                <a href="{{ route('tyro-dashboard.roles.show', $roleStat['id']) }}" style="text-decoration: none;">
                                    <span class="badge badge-primary" style="font-size: 0.875rem;">{{ $roleStat['name'] }}</span>
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 0.9375rem;">{{ $roleStat['count'] }}</strong> <span style="font-size: 0.9375rem;">users</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p class="empty-state-description" style="font-size: 0.9375rem;">No roles found.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@elseif($isOrgAdmin)
<div class="stats-grid">
    <a href="{{ route('tyro-dashboard.users.index') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Organization Users</div>
            <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
        </div>
    </a>

    <a href="{{ route('dashboard.my-meetings') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Upcoming Meetings</div>
            <div class="stat-value">{{ $upcomingMeetings->count() }}</div>
        </div>
    </a>

    <a href="{{ route('dashboard.my-meetings') }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Live Meetings</div>
            <div class="stat-value">{{ $liveMeetings->count() }}</div>
        </div>
    </a>

    <a href="{{ route('tyro-dashboard.users.index', ['status' => 'suspended']) }}" class="stat-card" style="text-decoration:none; color:inherit;">
        <div class="stat-icon stat-icon-danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-label" style="font-size: 0.9375rem;">Suspended Users</div>
            <div class="stat-value">{{ number_format($stats['suspended_users'] ?? 0) }}</div>
        </div>
    </a>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Organization Overview</h3>
            <a href="{{ route('dashboard.team.index') }}" class="btn btn-sm btn-ghost">Manage Team</a>
        </div>
        <div class="card-body">
            <div style="display:grid; gap: 14px;">
                <div>
                    <div style="font-size: 0.8125rem; color: var(--muted-foreground);">Organization</div>
                    <div style="font-size: 1rem; font-weight: 600;">{{ $user->organization->name ?? 'Your Organization' }}</div>
                </div>
                <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                    <div>
                        <div style="font-size: 0.8125rem; color: var(--muted-foreground);">Active Team Members</div>
                        <div style="font-size: 1.125rem; font-weight: 700;">{{ max(($stats['total_users'] ?? 0) - ($stats['suspended_users'] ?? 0), 0) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8125rem; color: var(--muted-foreground);">Total Participants</div>
                        <div style="font-size: 1.125rem; font-weight: 700;">{{ $totalParticipants }}</div>
                    </div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <a href="{{ route('dashboard.team.index') }}" class="btn btn-sm btn-primary">Manage Team</a>
                    <a href="{{ route('dashboard.subscription') }}" class="btn btn-sm btn-secondary">Subscription</a>
                    <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-sm btn-ghost">Schedule Meeting</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="font-size: 1.0625rem;">Upcoming Meetings</h3>
            <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-sm btn-ghost">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($upcomingMeetings->count())
            <div class="table-container">
                <table class="table">
                    <tbody>
                        @foreach($upcomingMeetings->take(5) as $meeting)
                        <tr>
                            <td>
                                <div>
                                    <div style="font-size: 0.9375rem; font-weight: 600;">{{ $meeting->title }}</div>
                                    <div style="font-size: 0.8125rem; color: var(--muted-foreground);">
                                        @if($meeting->start_at)
                                            {{ $meeting->start_at->format('M d, g:i A') }} · {{ $meeting->start_at->diffForHumans() }}
                                        @else
                                            Instant meeting
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-ghost" target="_blank">Details</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <p class="empty-state-description" style="font-size: 0.9375rem;">No upcoming meetings.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
@endsection
