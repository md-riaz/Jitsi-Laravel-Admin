@extends('tyro-dashboard::layouts.app')

@section('title', 'Pending Registrations')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Pending Registrations</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Pending Registrations</h1>
            <p class="page-description">Review and approve users who have requested to join <strong>{{ $organization->name }}</strong>.</p>
        </div>
        <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Team
        </a>
    </div>
</div>

@if(session('success'))
    <div style="padding: 14px 18px; margin-bottom: 20px; background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; border-radius: 6px;">
        <strong>Success!</strong> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 14px 18px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong> {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Pending Users
            @if($pendingUsers->count())
                <span style="display: inline-flex; align-items: center; justify-content: center; background: #f59e0b; color: #fff; border-radius: 999px; font-size: 0.75rem; font-weight: 700; min-width: 22px; height: 22px; padding: 0 7px; margin-left: 8px;">{{ $pendingUsers->count() }}</span>
            @endif
        </h3>
    </div>

    @if($pendingUsers->isEmpty())
        <div class="card-body" style="text-align: center; padding: 3rem 1rem;">
            <svg style="width: 48px; height: 48px; color: #9ca3af; margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p style="color: #6b7280; font-size: 1rem;">No pending registrations. You're all caught up!</p>
        </div>
    @else
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Requested</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingUsers as $pendingUser)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #374151; flex-shrink: 0; font-size: 0.9rem;">
                                    {{ strtoupper(substr($pendingUser->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #111827;">{{ $pendingUser->name }}</div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">{{ $pendingUser->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: #6b7280; font-size: 0.875rem;">
                            {{ $pendingUser->created_at->diffForHumans() }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <!-- Approve -->
                                <form method="POST" action="{{ route('dashboard.pending-users.approve', $pendingUser->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Approve {{ e($pendingUser->name) }}?')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px; display: inline-block;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                </form>

                                <!-- Reject -->
                                <form method="POST" action="{{ route('dashboard.pending-users.reject', $pendingUser->id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Reject and remove {{ e($pendingUser->name) }}\'s registration? This cannot be undone.')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 4px; display: inline-block;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
