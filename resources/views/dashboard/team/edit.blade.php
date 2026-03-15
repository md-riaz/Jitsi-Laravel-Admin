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
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-description" style="font-size: 1rem;">Update {{ $teamMember->name }}'s profile, role, and account status.</p>
        </div>
        <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">
            &larr; Back to Users
        </a>
    </div>
</div>

@if($errors->any())
    <div style="padding: 16px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong>
        <ul style="margin: 8px 0 0 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div style="padding: 16px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong> {{ session('error') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Left: User Profile & Role -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.team.update', $teamMember->id) }}">
                @csrf
                @method('PUT')

                <div style="display: grid; gap: 20px;">
                    <!-- Name -->
                    <div>
                        <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $teamMember->name) }}" required
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                               placeholder="Full name">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $teamMember->email) }}" required
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                               placeholder="email@example.com">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">
                            New Password <span style="color: #9ca3af; font-weight: normal;">(leave blank to keep current)</span>
                        </label>
                        <input type="password" id="password" name="password" minlength="8"
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                               placeholder="Minimum 8 characters">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" style="display: block; margin-bottom: 8px; font-weight: 500;">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                               style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                               placeholder="Re-enter new password">
                    </div>

                    <!-- Role -->
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Role *</label>
                        @php
                            $currentRole = $teamMember->organization->users()->where('user_id', $teamMember->id)->first()?->pivot->role ?? 'member';
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="role" value="member" {{ old('role', $currentRole) == 'member' ? 'checked' : '' }} required style="margin-top: 4px;">
                                <div>
                                    <strong style="display: block;">Member</strong>
                                    <small style="color: #6b7280;">Can join meetings; cannot create or manage meetings.</small>
                                </div>
                            </label>
                            <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="role" value="host" {{ old('role', $currentRole) == 'host' ? 'checked' : '' }} required style="margin-top: 4px;">
                                <div>
                                    <strong style="display: block;">Host</strong>
                                    <small style="color: #6b7280;">Can create and manage their own meetings and invite participants.</small>
                                </div>
                            </label>
                            <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                                <input type="radio" name="role" value="admin" {{ old('role', $currentRole) == 'admin' ? 'checked' : '' }} required style="margin-top: 4px;">
                                <div>
                                    <strong style="display: block;">Admin</strong>
                                    <small style="color: #6b7280;">Can manage users, create meetings, and manage all organization settings.</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                        <a href="{{ route('dashboard.team.index') }}"
                           style="padding: 10px 24px; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; color: #374151; background: white;">
                            Cancel
                        </a>
                        <button type="submit"
                                style="padding: 10px 24px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Account Status & Actions -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- Account Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Account Status</h3>
            </div>
            <div class="card-body">
                @if(method_exists($teamMember, 'isSuspended') && $teamMember->isSuspended())
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span>
                        <strong style="color: #991b1b;">Suspended</strong>
                    </div>
                    @if(method_exists($teamMember, 'getSuspensionReason') && $teamMember->getSuspensionReason())
                        <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 16px;">
                            <strong>Reason:</strong> {{ $teamMember->getSuspensionReason() }}
                        </p>
                    @endif
                    <form action="{{ route('dashboard.team.unsuspend', $teamMember->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width: 100%;">
                            Unsuspend User
                        </button>
                    </form>
                @else
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></span>
                        <strong style="color: #065f46;">Active</strong>
                    </div>
                    @if(method_exists($teamMember, 'suspend'))
                        <button type="button" class="btn btn-danger" style="width: 100%;"
                                onclick="document.getElementById('suspendSection').style.display = 'block'; this.style.display = 'none';">
                            Suspend User
                        </button>
                        <div id="suspendSection" style="display:none; margin-top:12px;">
                            <form action="{{ route('dashboard.team.suspend', $teamMember->id) }}" method="POST">
                                @csrf
                                <div style="margin-bottom: 12px;">
                                    <label style="display: block; margin-bottom: 6px; font-size: 0.875rem; font-weight: 500;">
                                        Reason <span style="color: #9ca3af;">(optional)</span>
                                    </label>
                                    <textarea name="reason" rows="3" placeholder="Enter a reason for suspension…"
                                              style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" class="btn btn-secondary" style="flex: 1;"
                                            onclick="document.getElementById('suspendSection').style.display = 'none'; document.querySelector('.btn-danger').style.display = '';">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-danger" style="flex: 1;">Confirm Suspend</button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Login As -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Impersonation</h3>
            </div>
            <div class="card-body">
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                    Log in as this user to troubleshoot issues or verify their experience. You can return to your account via the banner at the top of the page.
                </p>
                <form action="{{ route('dashboard.team.login-as', $teamMember->id) }}" method="POST"
                      id="login-as-form">
                    @csrf
                    <button type="button" class="btn btn-secondary" style="width: 100%;"
                            data-name="{{ e($teamMember->name) }}"
                            onclick="if(confirm('Log in as ' + this.dataset.name + '?')) document.getElementById('login-as-form').submit();">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 6px; vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Login As {{ $teamMember->name }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Danger Zone: Remove from Org -->
        <div class="card" style="border-color: #fca5a5;">
            <div class="card-header" style="border-bottom-color: #fca5a5;">
                <h3 class="card-title" style="color: #991b1b;">Danger Zone</h3>
            </div>
            <div class="card-body">
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                    Removing this user from the organization will revoke their org access. Their account will remain but they will no longer belong to this organization.
                </p>
                <form action="{{ route('dashboard.team.destroy', $teamMember->id) }}" method="POST"
                      id="remove-from-org-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger" style="width: 100%;"
                            data-name="{{ e($teamMember->name) }}"
                            onclick="if(confirm('Remove ' + this.dataset.name + ' from the organization? This cannot be undone.')) document.getElementById('remove-from-org-form').submit();">
                        Remove from Organization
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
