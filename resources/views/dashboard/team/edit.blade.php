@extends('tyro-dashboard::layouts.app')

@section('title', 'Edit Team Member')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.team.index') }}">Team Management</a>
<span class="breadcrumb-separator">/</span>
<span>Edit Member</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Team Member Role</h1>
            <p class="page-description" style="font-size: 1rem;">Update {{ $teamMember->name }}'s role in the organization.</p>
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

@if(session('error'))
    <div style="padding: 16px; margin-bottom: 20px; background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; border-radius: 6px;">
        <strong>Error!</strong> {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.team.update', $teamMember->id) }}">
            @csrf
            @method('PUT')

            <div style="display: grid; gap: 20px;">
                <!-- Member Info -->
                <div style="padding: 16px; background: #f9fafb; border-radius: 6px;">
                    <div style="display: grid; gap: 8px;">
                        <div>
                            <strong style="color: #374151;">Name:</strong>
                            <span style="color: #6b7280;">{{ $teamMember->name }}</span>
                        </div>
                        <div>
                            <strong style="color: #374151;">Email:</strong>
                            <span style="color: #6b7280;">{{ $teamMember->email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Role *</label>
                    @php
                        $currentRole = $teamMember->organization->users()->where('user_id', $teamMember->id)->first()->pivot->role ?? 'member';
                    @endphp
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="role" value="member" {{ old('role', $currentRole) == 'member' ? 'checked' : '' }} required style="margin-top: 4px;">
                            <div style="flex: 1;">
                                <strong style="display: block;">Member</strong>
                                <small style="color: #6b7280;">Can create and manage their own meetings within the organization.</small>
                            </div>
                        </label>
                        <label style="display: flex; align-items: start; gap: 12px; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer;">
                            <input type="radio" name="role" value="admin" {{ old('role', $currentRole) == 'admin' ? 'checked' : '' }} required style="margin-top: 4px;">
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
                        Update Role
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
