@extends('tyro-dashboard::layouts.app')

@section('title', 'Add Team Member')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.team.index') }}">Team Management</a>
<span class="breadcrumb-separator">/</span>
<span>Add Team Member</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Team Member</h1>
            <p class="page-description" style="font-size: 1rem;">Invite a new member to join your organization.</p>
        </div>
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

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.team.store') }}">
            @csrf

            <div style="display: grid; gap: 20px;">
                <!-- Name Field -->
                <div>
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           placeholder="Enter member's full name">
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           placeholder="email@example.com">
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500;">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           placeholder="Minimum 8 characters">
                    <small style="color: #6b7280;">The member will use this password to log in.</small>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" style="display: block; margin-bottom: 8px; font-weight: 500;">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                           style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;"
                           placeholder="Re-enter password">
                </div>

                <!-- Role Selection -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Role *</label>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="role" value="member" {{ old('role', 'member') == 'member' ? 'checked' : '' }} required style="margin-top: 4px;">
                            <div style="flex: 1;">
                                <strong style="display: block;">Member</strong>
                                <small style="color: #6b7280;">Can join meetings they are invited to. Cannot create or manage meetings.</small>
                            </div>
                        </label>
                        <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="role" value="host" {{ old('role') == 'host' ? 'checked' : '' }} required style="margin-top: 4px;">
                            <div style="flex: 1;">
                                <strong style="display: block;">Host</strong>
                                <small style="color: #6b7280;">Can create and manage their own meetings, invite participants, and moderate sessions.</small>
                            </div>
                        </label>
                        <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="role" value="admin" {{ old('role') == 'admin' ? 'checked' : '' }} required style="margin-top: 4px;">
                            <div style="flex: 1;">
                                <strong style="display: block;">Admin</strong>
                                <small style="color: #6b7280;">Can manage team members, create meetings, and manage all organization settings.</small>
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
                        Add Team Member
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
