@extends('tyro-dashboard::layouts.app')

@section('title', 'My Meetings')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>My Meetings</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Meetings</h1>
            <p class="page-description" style="font-size: 1rem;">View and manage your upcoming and past meetings.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Meeting
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Upcoming Meetings</h3>
    </div>
    <div class="card-body">
        @if($upcomingMeetings->isEmpty())
            <p style="color: #6b7280; text-align: center; padding: 40px;">No upcoming meetings scheduled.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingMeetings as $meeting)
                        <tr>
                            <td>
                                <strong>{{ $meeting->title }}</strong>
                                @if($meeting->description)
                                    <br><small style="color: #6b7280;">{{ Str::limit($meeting->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $meeting->start_at->format('M d, Y g:i A') }}
                                </div>
                                <small style="color: #6b7280;">{{ $meeting->start_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @if($meeting->canJoinAt(now()))
                                    <span class="badge badge-success">Live</span>
                                @else
                                    <span class="badge badge-warning">Upcoming</span>
                                @endif
                            </td>
                            <td>{{ $meeting->participants->count() }} participant(s)</td>
                            <td>
                                <a href="{{ route('meeting.show', $meeting->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                    View Meeting
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Past Meetings</h3>
    </div>
    <div class="card-body">
        @if($pastMeetings->isEmpty())
            <p style="color: #6b7280; text-align: center; padding: 40px;">No past meetings.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Meeting</th>
                            <th>Date & Time</th>
                            <th>Participants</th>
                            <th>Actions</th>
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
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $meeting->start_at->format('M d, Y g:i A') }}
                                </div>
                                <small style="color: #6b7280;">{{ $meeting->start_at->diffForHumans() }}</small>
                            </td>
                            <td>{{ $meeting->participants->count() }} participant(s)</td>
                            <td>
                                <span class="badge badge-secondary">Ended</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

