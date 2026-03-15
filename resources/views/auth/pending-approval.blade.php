@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container" style="justify-content: center; align-items: center; min-height: 100vh; background: #f3f4f6;">
    <div style="max-width: 480px; width: 100%; margin: 2rem auto; padding: 0 1rem;">
        <div class="form-card" style="text-align: center; padding: 3rem 2rem;">
            <!-- Icon -->
            <div style="margin-bottom: 1.5rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; background: #fef3c7; border-radius: 50%; margin: 0 auto;">
                    <svg style="width: 36px; height: 36px; color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.75rem;">Registration Pending Approval</h2>

            <p style="color: #6b7280; margin-bottom: 1.5rem; line-height: 1.6;">
                Your registration request has been submitted. An <strong>Organization Admin</strong> will review and approve your account shortly.
            </p>

            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 2rem; text-align: left;">
                <p style="font-size: 0.875rem; color: #92400e; margin: 0;">
                    <strong>What happens next?</strong><br>
                    Once your account is approved, you will be able to log in and access your organization's workspace. You may need to log in again after approval.
                </p>
            </div>

            <!-- Logout link -->
            <form method="POST" action="{{ route('tyro-login.logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width: 100%;">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
