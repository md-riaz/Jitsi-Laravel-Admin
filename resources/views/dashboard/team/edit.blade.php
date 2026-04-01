@extends('tyro-dashboard::layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.team.index') }}">User Management</a>
<span class="breadcrumb-separator">/</span>
<span>Edit User</span>
@endsection

@section('content')
<style>
.team-edit-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.team-edit-form-grid {
    display: grid;
    gap: 1.25rem;
}

.team-role-options,
.team-actions-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.team-role-card {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 0.5rem;
    cursor: pointer;
}

.team-role-card-content {
    flex: 1;
}

.team-actions-stack {
    gap: 1.25rem;
}

.team-status-row {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 1rem;
}

.team-status-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.team-status-dot-danger { background: var(--destructive); }
.team-status-dot-success { background: var(--success); }

.team-divider {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
}

.team-hidden {
    display: none;
}

.team-button-full {
    width: 100%;
}

.team-flex-row {
    display: flex;
    gap: 0.5rem;
}

.team-flex-1 {
    flex: 1;
}

.team-danger-card {
    border-color: color-mix(in srgb, var(--destructive), transparent 55%);
}

.team-danger-card .card-header {
    border-bottom-color: color-mix(in srgb, var(--destructive), transparent 55%);
}

.team-danger-title {
    color: var(--destructive);
}

@media (max-width: 1024px) {
    .team-edit-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-description">Update {{ $teamMember->name }}'s profile, role, and account status.</p>
        </div>
        <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">
            &larr; Back to Users
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error">
        <div class="alert-content">
            <div class="alert-title">Error!</div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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

@php
    $organization = $teamMember->organization;
    $memberIsOwner = $organization
        && $organization->owner_id !== null
        && (int) $organization->owner_id === (int) $teamMember->id;

    $currentUser = auth()->user();
    $currentUserIsSuperAdmin = $currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole('super-admin');
    $currentUserIsOwner = $organization
        && $organization->owner_id !== null
        && $currentUser
        && (int) $organization->owner_id === (int) $currentUser->id;

    $memberIsOrgAdmin = $teamMember->hasRole('org-admin');
    $ownerRestricted = $memberIsOwner || (!$currentUserIsSuperAdmin && !$currentUserIsOwner && $memberIsOrgAdmin);
@endphp

<div class="team-edit-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.team.update', $teamMember->id) }}">
                @csrf
                @method('PUT')

                <div class="team-edit-form-grid">
                    <div>
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $teamMember->name) }}" required class="form-input" placeholder="Full name">
                    </div>

                    <div>
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $teamMember->email) }}" required class="form-input" placeholder="[email]">
                    </div>

                    <div>
                        <label for="password" class="form-label">
                            New Password <span class="form-label-optional">(leave blank to keep current)</span>
                        </label>
                        <input type="password" id="password" name="password" minlength="8" class="form-input" placeholder="Minimum 8 characters">
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" class="form-input" placeholder="Re-enter new password">
                    </div>

                    <div>
                        <label class="form-label">Role *</label>
                        @php
                            $currentRole = $teamMember->organization->users()->where('user_id', $teamMember->id)->first()?->pivot->role ?? 'member';
                        @endphp
                        @if($memberIsOwner)
                            <p class="form-hint" style="margin-bottom: 0.5rem;">This account is the organization owner. Role changes are blocked.</p>
                        @elseif(!$currentUserIsOwner && $memberIsOrgAdmin)
                            <p class="form-hint" style="margin-bottom: 0.5rem;">Only the organization owner can change another admin's role.</p>
                        @endif
                        <div class="team-role-options">
                            <label class="team-role-card">
                                <input type="radio" name="role" value="member" {{ old('role', $currentRole) == 'member' ? 'checked' : '' }} required {{ $ownerRestricted ? 'disabled' : '' }}>
                                <div class="team-role-card-content">
                                    <strong>Member</strong>
                                    <p class="form-hint">Can join meetings; cannot create or manage meetings.</p>
                                </div>
                            </label>
                            <label class="team-role-card">
                                <input type="radio" name="role" value="host" {{ old('role', $currentRole) == 'host' ? 'checked' : '' }} required {{ $ownerRestricted ? 'disabled' : '' }}>
                                <div class="team-role-card-content">
                                    <strong>Host</strong>
                                    <p class="form-hint">Can create and manage their own meetings and invite participants.</p>
                                </div>
                            </label>
                            <label class="team-role-card">
                                <input type="radio" name="role" value="admin" {{ old('role', $currentRole) == 'admin' ? 'checked' : '' }} required {{ $ownerRestricted ? 'disabled' : '' }}>
                                <div class="team-role-card-content">
                                    <strong>Admin</strong>
                                    <p class="form-hint">Can manage users, create meetings, and manage all organization settings.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="team-divider">
                        <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" {{ $ownerRestricted ? 'disabled' : '' }}>Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="team-actions-stack">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Status</h3>
            </div>
            <div class="card-body">
                @if($ownerRestricted)
                    <p class="form-hint">Owner boundary protection is active. Suspension actions are disabled for this account.</p>
                @elseif(method_exists($teamMember, 'isSuspended') && $teamMember->isSuspended())
                    <div class="team-status-row">
                        <span class="team-status-dot team-status-dot-danger"></span>
                        <strong class="team-danger-title">Suspended</strong>
                    </div>
                    @if(method_exists($teamMember, 'getSuspensionReason') && $teamMember->getSuspensionReason())
                        <p class="form-hint"><strong>Reason:</strong> {{ $teamMember->getSuspensionReason() }}</p>
                    @endif
                    <form action="{{ route('dashboard.team.unsuspend', $teamMember->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary team-button-full">Unsuspend User</button>
                    </form>
                @else
                    <div class="team-status-row">
                        <span class="team-status-dot team-status-dot-success"></span>
                        <strong>Active</strong>
                    </div>
                    @if(method_exists($teamMember, 'suspend'))
                        <button type="button" class="btn btn-destructive team-button-full" onclick="document.getElementById('suspendSection').classList.remove('team-hidden'); this.classList.add('team-hidden');">
                            Suspend User
                        </button>
                        <div id="suspendSection" class="team-hidden" style="margin-top: 0.75rem;">
                            <form action="{{ route('dashboard.team.suspend', $teamMember->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">
                                        Reason <span class="form-label-optional">(optional)</span>
                                    </label>
                                    <textarea name="reason" rows="3" placeholder="Enter a reason for suspension…" class="form-textarea"></textarea>
                                </div>
                                <div class="team-flex-row">
                                    <button type="button" class="btn btn-secondary team-flex-1" onclick="document.getElementById('suspendSection').classList.add('team-hidden'); this.closest('.card-body').querySelector('.btn.btn-destructive').classList.remove('team-hidden');">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-destructive team-flex-1">Confirm Suspend</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impersonation</h3>
            </div>
            <div class="card-body">
                @if($ownerRestricted)
                    <p class="form-hint">Owner boundary protection is active. Impersonation is disabled for this account.</p>
                @else
                    <p class="form-hint">Log in as this user to troubleshoot issues or verify their experience. You can return to your account via the banner at the top of the page.</p>
                    <form action="{{ route('dashboard.team.login-as', $teamMember->id) }}" method="POST" id="login-as-form">
                        @csrf
                        <button type="button" class="btn btn-secondary team-button-full" data-name="{{ e($teamMember->name) }}" onclick="if(confirm('Log in as ' + this.dataset.name + '?')) document.getElementById('login-as-form').submit();">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Login As {{ $teamMember->name }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card team-danger-card">
            <div class="card-header">
                <h3 class="card-title team-danger-title">Danger Zone</h3>
            </div>
            <div class="card-body">
                @if($ownerRestricted)
                    <p class="form-hint">Owner boundary protection is active. Removal is disabled for this account.</p>
                @else
                    <p class="form-hint">Removing this user from the organization will revoke their org access. Their account will remain but they will no longer belong to this organization.</p>
                    <form action="{{ route('dashboard.team.destroy', $teamMember->id) }}" method="POST" id="remove-from-org-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-destructive team-button-full" data-name="{{ e($teamMember->name) }}" onclick="if(confirm('Remove ' + this.dataset.name + ' from the organization? This cannot be undone.')) document.getElementById('remove-from-org-form').submit();">
                            Remove from Organization
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
