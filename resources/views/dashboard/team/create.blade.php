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
<style>
.team-form-grid {
    display: grid;
    gap: 1.25rem;
}

.team-form-choices {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.team-role-options {
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

.team-inline-choice {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Team Member</h1>
            <p class="page-description">Invite a member into your organization. Super admins can either create a brand-new organization with its first admin or add a user to an existing organization.</p>
        </div>
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

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.team.store') }}" class="team-create-form">
            @csrf

            <div class="team-form-grid">
                @if(auth()->user()?->hasRole('super-admin'))
                    <div>
                        <label class="form-label">Provisioning Mode *</label>
                        <div class="team-form-choices">
                            <label class="team-inline-choice">
                                <input type="radio" name="provisioning_mode" value="new" {{ old('provisioning_mode', 'new') === 'new' ? 'checked' : '' }}>
                                <span>Create new organization + first admin</span>
                            </label>
                            <label class="team-inline-choice">
                                <input type="radio" name="provisioning_mode" value="existing" {{ old('provisioning_mode') === 'existing' ? 'checked' : '' }}>
                                <span>Add user to an existing organization</span>
                            </label>
                        </div>
                    </div>

                    <div id="new-org-fields" class="{{ old('provisioning_mode', 'new') === 'new' ? '' : 'team-hidden' }}">
                        <label for="organization_name" class="form-label">Organization Name *</label>
                        <input type="text" id="organization_name" name="organization_name" value="{{ old('organization_name') }}" class="form-input" placeholder="Enter the new organization's name">
                        <p class="form-hint">This creates a new organization and sets this user as its initial admin.</p>
                    </div>

                    <div id="existing-org-fields" class="{{ old('provisioning_mode') === 'existing' ? '' : 'team-hidden' }}">
                        <label for="organization_id" class="form-label">Existing Organization *</label>
                        <select id="organization_id" name="organization_id" class="form-select">
                            <option value="">Select organization</option>
                            @foreach(($organizations ?? collect()) as $organization)
                                <option value="{{ $organization->id }}" {{ old('organization_id') == $organization->id ? 'selected' : '' }}>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-hint">The new user will be created under this organization.</p>
                    </div>
                @else
                    <div>
                        <label class="form-label">Organization Context</label>
                        <input type="text" value="{{ auth()->user()?->organization?->name ?? 'N/A' }}" readonly class="form-input">
                        <p class="form-hint">This user will be created in your organization.</p>
                    </div>
                @endif

                <div>
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Enter member's full name">
                </div>

                <div>
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="[email]">
                </div>

                <div>
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8" class="form-input" placeholder="Minimum 8 characters">
                    <p class="form-hint">The member will use this password to log in.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" class="form-input" placeholder="Re-enter password">
                </div>

                <div>
                    <label class="form-label">Role *</label>
                    <div class="team-role-options">
                        @if(!auth()->user()?->hasRole('super-admin'))
                        <label class="team-role-card">
                            <input type="radio" name="role" value="member" {{ old('role', 'member') == 'member' ? 'checked' : '' }} required>
                            <div class="team-role-card-content">
                                <strong>Member</strong>
                                <p class="form-hint">Can join meetings they are invited to. Cannot create or manage meetings.</p>
                            </div>
                        </label>
                        <label class="team-role-card">
                            <input type="radio" name="role" value="host" {{ old('role') == 'host' ? 'checked' : '' }} required>
                            <div class="team-role-card-content">
                                <strong>Host</strong>
                                <p class="form-hint">Can create and manage their own meetings, invite participants, and moderate sessions.</p>
                            </div>
                        </label>
                        @endif
                        <label class="team-role-card">
                            <input type="radio" name="role" value="admin" {{ old('role', auth()->user()?->hasRole('super-admin') ? 'admin' : null) == 'admin' ? 'checked' : '' }} required>
                            <div class="team-role-card-content">
                                <strong>Admin</strong>
                                <p class="form-hint">Can manage team members, create meetings, and manage all organization settings.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="team-divider">
                    <a href="{{ route('dashboard.team.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" id="team-submit-button" class="btn btn-primary">
                        <span id="team-submit-button-label">Add Team Member</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()?->hasRole('super-admin'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeInputs = document.querySelectorAll('input[name="provisioning_mode"]');
    const newOrgFields = document.getElementById('new-org-fields');
    const existingOrgFields = document.getElementById('existing-org-fields');
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const submitLabel = document.getElementById('team-submit-button-label');

    const applyMode = () => {
        const mode = document.querySelector('input[name="provisioning_mode"]:checked')?.value || 'new';
        const isNew = mode === 'new';

        if (newOrgFields) newOrgFields.classList.toggle('team-hidden', !isNew);
        if (existingOrgFields) existingOrgFields.classList.toggle('team-hidden', isNew);

        roleInputs.forEach((input) => {
            if (input.value === 'admin') {
                if (isNew) {
                    input.checked = true;
                }
                input.disabled = false;
            } else {
                input.disabled = isNew;
            }
        });

        if (submitLabel) {
            submitLabel.textContent = isNew ? 'Create Organization Admin' : 'Add User to Organization';
        }
    };

    modeInputs.forEach((input) => input.addEventListener('change', applyMode));
    applyMode();
});
</script>
@endif
@endsection
