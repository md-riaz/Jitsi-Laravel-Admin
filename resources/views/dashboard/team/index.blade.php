@extends('tyro-dashboard::layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>User Management</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-description" style="font-size: 1rem;">Manage your organization's users, roles, and account status.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.team.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
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

<!-- Search & Filter -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form method="GET" action="{{ route('dashboard.team.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 4px; font-size: 0.875rem; font-weight: 500;">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…"
                       style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
            </div>
            <div style="min-width: 150px;">
                <label style="display: block; margin-bottom: 4px; font-size: 0.875rem; font-weight: 500;">Role</label>
                <select name="role" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="host" {{ request('role') === 'host' ? 'selected' : '' }}>Host</option>
                    <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Member</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-secondary" style="padding: 8px 16px;">Filter</button>
                @if(request('search') || request('role'))
                    <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary" style="padding: 8px 16px;">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">
            {{ $organization->name }} — Users ({{ $teamMembers->count() }})
        </h3>
    </div>
    <div class="card-body">
        @if($teamMembers->isEmpty())
            <p style="color: #6b7280; text-align: center; padding: 40px;">No users found. Add your first user!</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teamMembers as $member)
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <strong>{{ $member->name }}</strong>
                                    <span style="color: #6b7280; font-size: 0.875rem;">{{ $member->email }}</span>
                                </div>
                                @if($member->id === auth()->id())
                                    <span class="badge badge-info" style="margin-top: 4px;">You</span>
                                @endif
                            </td>
                            <td>
                                @if($member->pivot->role === 'admin')
                                    <span class="badge badge-primary">Admin</span>
                                @elseif($member->pivot->role === 'host')
                                    <span class="badge badge-info">Host</span>
                                @else
                                    <span class="badge badge-secondary">Member</span>
                                @endif
                            </td>
                            <td>
                                @if(method_exists($member, 'isSuspended') && $member->isSuspended())
                                    <span class="badge badge-danger" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">Suspended</span>
                                @else
                                    <span class="badge badge-success" style="background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">Active</span>
                                @endif
                            </td>
                            <td>{{ $member->pivot->created_at ? $member->pivot->created_at->format('M d, Y') : '—' }}</td>
                            <td>
                                @if($member->id !== auth()->id())
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <!-- Edit -->
                                        <a href="{{ route('dashboard.team.edit', $member->id) }}" class="btn btn-sm btn-secondary" title="Edit user">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <!-- Login As -->
                                        <form action="{{ route('dashboard.team.login-as', $member->id) }}" method="POST" style="display: inline;" id="login-as-form-{{ $member->id }}">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-secondary" title="Login as this user"
                                                    data-name="{{ e($member->name) }}"
                                                    onclick="if(confirm('Log in as ' + this.dataset.name + '?')) document.getElementById('login-as-form-{{ $member->id }}').submit();">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Login As
                                            </button>
                                        </form>

                                        <!-- Suspend / Unsuspend -->
                                        @if(method_exists($member, 'isSuspended') && $member->isSuspended())
                                            <form action="{{ route('dashboard.team.unsuspend', $member->id) }}" method="POST" style="display: inline;" id="unsuspend-form-{{ $member->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-secondary" title="Unsuspend user"
                                                        data-name="{{ e($member->name) }}"
                                                        onclick="if(confirm('Unsuspend ' + this.dataset.name + '?')) document.getElementById('unsuspend-form-{{ $member->id }}').submit();">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Unsuspend
                                                </button>
                                            </form>
                                        @elseif(method_exists($member, 'suspend'))
                                            <button type="button" class="btn btn-sm btn-danger" title="Suspend user"
                                                    data-user-id="{{ $member->id }}"
                                                    data-name="{{ e($member->name) }}"
                                                    data-suspend-url="{{ route('dashboard.team.suspend', $member->id) }}"
                                                    onclick="openSuspendModal(this.dataset.userId, this.dataset.name, this.dataset.suspendUrl)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Suspend
                                            </button>
                                        @endif

                                        <!-- Remove -->
                                        <form action="{{ route('dashboard.team.destroy', $member->id) }}" method="POST" style="display: inline;" id="remove-form-{{ $member->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger" title="Remove from organization"
                                                    data-name="{{ e($member->name) }}"
                                                    onclick="if(confirm('Remove ' + this.dataset.name + ' from the organization?')) document.getElementById('remove-form-{{ $member->id }}').submit();">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zm7-7l5 5m0-5l-5 5"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
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

<!-- Suspend Modal -->
<div id="suspendModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; padding:24px; max-width:460px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="margin:0 0 8px;">Suspend User</h3>
        <p id="suspendModalText" style="color:#6b7280; margin-bottom:16px;"></p>
        <form id="suspendForm" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-weight:500;">Reason <span style="color:#9ca3af;">(optional)</span></label>
                <textarea name="reason" rows="3" placeholder="Enter a reason for suspension…"
                          style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; resize:vertical;"></textarea>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeSuspendModal()"
                        style="padding:8px 20px; border:1px solid #d1d5db; border-radius:6px; background:white; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-danger">Suspend User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSuspendModal(userId, userName, suspendUrl) {
    document.getElementById('suspendModalText').textContent =
        'You are about to suspend ' + userName + '. They will be unable to log in until unsuspended.';
    document.getElementById('suspendForm').action = suspendUrl;
    const modal = document.getElementById('suspendModal');
    modal.style.display = 'flex';
}
function closeSuspendModal() {
    document.getElementById('suspendModal').style.display = 'none';
}
document.getElementById('suspendModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuspendModal();
});
</script>
@endsection
