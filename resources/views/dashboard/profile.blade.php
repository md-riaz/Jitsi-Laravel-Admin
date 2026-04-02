@extends('tyro-dashboard::layouts.app')

@section('title', 'Profile')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Profile</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Profile</h1>
            <p class="page-description" style="font-size: 1rem;">Manage your profile settings and avatar.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Profile Picture</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 2rem; align-items: start;">
            <div style="flex-shrink: 0;">
                <img id="avatar-preview" src="{{ auth()->user()->avatar_url }}" alt="Profile Picture"
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
            </div>
            <div style="flex: 1;">
                <form id="avatar-upload-form" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label for="avatar" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Choose a new profile picture</label>
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif"
                               class="form-input" style="max-width: 400px;">
                        <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">JPG, PNG, or GIF. Max size 2MB.</p>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Upload Picture</button>
                        <button type="button" id="delete-avatar-btn" class="btn btn-danger">Remove Picture</button>
                    </div>
                    <div id="upload-message" style="margin-top: 1rem;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Profile Information</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; gap: 1rem;">
            <div>
                <strong>Name:</strong> {{ auth()->user()->name }}
            </div>
            <div>
                <strong>Email:</strong> {{ auth()->user()->email }}
            </div>
            <div>
                <strong>Account Type:</strong> {{ ucfirst(auth()->user()->account_type ?? 'N/A') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('avatar-upload-form');
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatar-preview');
    const deleteBtn = document.getElementById('delete-avatar-btn');
    const messageDiv = document.getElementById('upload-message');

    // Preview image before upload
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle upload
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData();
        const file = avatarInput.files[0];

        if (!file) {
            showMessage('Please select an image file', 'error');
            return;
        }

        formData.append('avatar', file);

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            const response = await fetch(@json(route('dashboard.profile.avatar.upload')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                showMessage(data.message, 'success');
                avatarPreview.src = data.avatar_url;
                avatarInput.value = '';
            } else {
                showMessage(data.message || 'Upload failed', 'error');
            }
        } catch (error) {
            showMessage('Error uploading image', 'error');
            console.error(error);
        }
    });

    // Handle delete
    deleteBtn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to remove your profile picture?')) {
            return;
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
            const response = await fetch(@json(route('dashboard.profile.avatar.delete')), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (response.ok) {
                showMessage(data.message, 'success');
                avatarPreview.src = data.avatar_url;
            } else {
                showMessage(data.message || 'Delete failed', 'error');
            }
        } catch (error) {
            showMessage('Error deleting image', 'error');
            console.error(error);
        }
    });

    function showMessage(message, type) {
        messageDiv.innerHTML = `<div style="padding: 0.75rem; border-radius: 0.375rem; background-color: ${type === 'success' ? '#d1fae5' : '#fee2e2'}; color: ${type === 'success' ? '#065f46' : '#991b1b'};">${message}</div>`;
        setTimeout(() => {
            messageDiv.innerHTML = '';
        }, 5000);
    }
});
</script>
@endsection
