@extends('tyro-dashboard::layouts.app')

@section('title', 'Team Management')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Team Management</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Team Management</h1>
            <p class="page-description" style="font-size: 1rem;">Manage your organization's team members and their roles.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.team.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Team Member
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="padding: 16px; margin-bottom: 20px; background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; border-radius: 6px;">
        <strong>Success!</strong> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding: 16px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong> {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">{{ $organization->name }} - Team Members ({{ $teamMembers->count() }})</h3>
    </div>
    <div class="card-body">
        @if($teamMembers->isEmpty())
            <p style="color: #6b7280; text-align: center; padding: 40px;">No team members yet. Add your first team member!</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teamMembers as $member)
                        <tr>
                            <td>
                                <strong>{{ $member->name }}</strong>
                                @if($member->id === auth()->id())
                                    <span class="badge badge-info" style="margin-left: 8px;">You</span>
                                @endif
                            </td>
                            <td>{{ $member->email }}</td>
                            <td>
                                @if($member->pivot->role === 'admin')
                                    <span class="badge badge-primary">Admin</span>
                                @else
                                    <span class="badge badge-secondary">Member</span>
                                @endif
                            </td>
                            <td>{{ $member->pivot->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($member->id !== auth()->id())
                                    <a href="{{ route('dashboard.team.edit', $member->id) }}" class="btn btn-sm btn-secondary" style="margin-right: 8px;">
                                        Edit Role
                                    </a>
                                    <form action="{{ route('dashboard.team.destroy', $member->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this team member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Remove
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #6b7280; font-size: 0.875rem;">—</span>
                                @endif
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
        <h3 class="card-title" style="font-size: 1.0625rem;">Role Descriptions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; gap: 16px;">
            <div>
                <strong style="color: #667eea;">Admin</strong>
                <p style="margin: 4px 0 0 0; color: #6b7280;">Can manage team members, create meetings, and manage all organization settings.</p>
            </div>
            <div>
                <strong style="color: #6b7280;">Member</strong>
                <p style="margin: 4px 0 0 0; color: #6b7280;">Can create and manage their own meetings within the organization.</p>
            </div>
        </div>
    </div>
</div>
@endsection
