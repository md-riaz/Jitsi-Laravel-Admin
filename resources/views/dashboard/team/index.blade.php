@extends('tyro-dashboard::layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>User Management</span>
@endsection

@section('content')
<style>
.team-index-form {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.team-index-filter-field {
    min-width: 150px;
}

.team-index-filter-field-grow {
    flex: 1;
    min-width: 200px;
}

.team-index-actions {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
}

.team-user-cell {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.team-user-email,
.team-muted {
    color: var(--muted-foreground);
    font-size: 0.875rem;
}

.team-row-actions {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
}

.team-inline-form {
    display: inline;
}

.team-status-suspended {
    background: color-mix(in srgb, var(--destructive), transparent 88%);
    color: color-mix(in srgb, var(--destructive), black 25%);
    border: 1px solid color-mix(in srgb, var(--destructive), transparent 45%);
}

.team-status-active {
    background: color-mix(in srgb, var(--success), transparent 88%);
    color: color-mix(in srgb, var(--success), black 30%);
    border: 1px solid color-mix(in srgb, var(--success), transparent 45%);
}

.team-no-action {
    color: var(--muted-foreground);
    font-size: 0.875rem;
}

.team-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.team-modal {
    background: var(--background);
    border-radius: 0.5rem;
    padding: 1.5rem;
    max-width: 460px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    border: 1px solid var(--border);
}

.team-modal-title {
    margin: 0 0 0.5rem;
}

.team-modal-text {
    color: var(--muted-foreground);
    margin-bottom: 1rem;
}

.team-modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">User Management</h1>
            <p class="page-description">Manage your organization's users, roles, and account status.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.team.create') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <div class="alert-content">
            <div class="alert-title">Success!</div>
            <p class="alert-message">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        <div class="alert-content">
            <div class="alert-title">Error!</div>
            <p class="alert-message">{{ session('error') }}</p>
        </div>
    </div>
@endif

<div class="card" style="margin-bottom: 1.25rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="{{ route('dashboard.team.index') }}" class="team-index-form">
            <div class="team-index-filter-field-grow">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…" class="form-input">
            </div>
            <div class="team-index-filter-field">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="host" {{ request('role') === 'host' ? 'selected' : '' }}>Host</option>
                    <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Member</option>
                </select>
            </div>
            <div class="team-index-filter-field">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                </select>
            </div>
            <div class="team-index-actions">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request('search') || request('role') || request('status'))
                    <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $organization->name }} — Users ({{ $teamMembers->count() }})</h3>
    </div>
    <div class="card-body">
        @if($teamMembers->isEmpty())
            <p class="team-muted" style="text-align: center; padding: 2.5rem 0;">No users found. Add your first user!</p>
        @else
            <div class="table-container">
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
                        @php
                            $memberIsOwner = $member->organization
                                && $member->organization->owner_id !== null
                                && (int) $member->organization->owner_id === (int) $member->id;

                            $currentUser = auth()->user();
                            $currentUserIsOwner = $organization
                                && $organization->owner_id !== null
                                && $currentUser
                                && (int) $organization->owner_id === (int) $currentUser->id;

                            $currentUserIsSuperAdmin = $currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');
                            $memberIsOrgAdmin = $member->hasRole('org-admin');
                            $canManageMember = $member->id !== auth()->id()
                                && !$memberIsOwner
                                && ($currentUserIsSuperAdmin || $currentUserIsOwner || !$memberIsOrgAdmin);
                        @endphp
                        <tr>
                            <td>
                                <div class="team-user-cell">
                                    <strong>{{ $member->name }}</strong>
                                    <span class="team-user-email">{{ $member->email }}</span>
                                </div>
                                @if($member->id === auth()->id())
                                    <span class="badge badge-info" style="margin-top: 0.25rem;">You</span>
                                @endif
                                @if($memberIsOwner)
                                    <span class="badge badge-warning" style="margin-top: 0.25rem;">Owner</span>
                                @endif
                            </td>
                            <td>
                                @if($member->hasRole('org-admin'))
                                    <span class="badge badge-primary">Admin</span>
                                @elseif($member->hasRole('host'))
                                    <span class="badge badge-info">Host</span>
                                @else
                                    <span class="badge badge-secondary">Member</span>
                                @endif
                            </td>
                            <td>
                                @if(method_exists($member, 'isSuspended') && $member->isSuspended())
                                    <span class="badge team-status-suspended">Suspended</span>
                                @else
                                    <span class="badge team-status-active">Active</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at ? $member->created_at->format('M d, Y') : '—' }}</td>
                            <td>
                                @if($canManageMember)
                                    <div class="team-row-actions">
                                        <a href="{{ route('dashboard.team.edit', $member->id) }}" class="btn btn-sm btn-secondary" title="Edit user">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form action="{{ route('dashboard.team.login-as', $member->id) }}" method="POST" class="team-inline-form" id="login-as-form-{{ $member->id }}">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-secondary" title="Login as this user" data-name="{{ e($member->name) }}" onclick="if(confirm('Log in as ' + this.dataset.name + '?')) document.getElementById('login-as-form-{{ $member->id }}').submit();">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Login As
                                            </button>
                                        </form>

                                        @if(method_exists($member, 'isSuspended') && $member->isSuspended())
                                            <form action="{{ route('dashboard.team.unsuspend', $member->id) }}" method="POST" class="team-inline-form" id="unsuspend-form-{{ $member->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-secondary" title="Unsuspend user" data-name="{{ e($member->name) }}" onclick="if(confirm('Unsuspend ' + this.dataset.name + '?')) document.getElementById('unsuspend-form-{{ $member->id }}').submit();">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Unsuspend
                                                </button>
                                            </form>
                                        @elseif(method_exists($member, 'suspend'))
                                            <button type="button" class="btn btn-sm btn-destructive" title="Suspend user" data-user-id="{{ $member->id }}" data-name="{{ e($member->name) }}" data-suspend-url="{{ route('dashboard.team.suspend', $member->id) }}" onclick="openSuspendModal(this.dataset.userId, this.dataset.name, this.dataset.suspendUrl)">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Suspend
                                            </button>
                                        @endif

                                        <form action="{{ route('dashboard.team.destroy', $member->id) }}" method="POST" class="team-inline-form" id="remove-form-{{ $member->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-destructive" title="Remove from organization" data-name="{{ e($member->name) }}" onclick="if(confirm('Remove ' + this.dataset.name + ' from the organization?')) document.getElementById('remove-form-{{ $member->id }}').submit();">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zm7-7l5 5m0-5l-5 5"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    @if($memberIsOwner)
                                        <span class="team-no-action">Owner account is protected</span>
                                    @elseif($member->id === auth()->id())
                                        <span class="team-no-action">—</span>
                                    @else
                                        <span class="team-no-action">Only owner can manage admin accounts</span>
                                    @endif
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

<div id="suspendModal" class="team-modal-overlay">
    <div class="team-modal">
        <h3 class="team-modal-title">Suspend User</h3>
        <p id="suspendModalText" class="team-modal-text"></p>
        <form id="suspendForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason <span class="form-label-optional">(optional)</span></label>
                <textarea name="reason" rows="3" placeholder="Enter a reason for suspension…" class="form-textarea"></textarea>
            </div>
            <div class="team-modal-actions">
                <button type="button" onclick="closeSuspendModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-destructive">Suspend User</button>
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
