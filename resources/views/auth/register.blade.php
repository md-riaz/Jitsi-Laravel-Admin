@extends('tyro-login::layouts.auth')

@section('content')
<div class="auth-container split-left">
    <div class="background-panel" style="background: #1d4ed8;">
        <div class="background-panel-content">
            <h1>Start Your Organization</h1>
            <p>Create your organization and its first admin account in one onboarding flow.</p>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="app-logo">
                    <svg viewBox="0 0 50 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M49.626 11.564a.809.809 0 0 1 .028.209v10.972a.8.8 0 0 1-.402.694l-9.209 5.302V39.25c0 .286-.152.55-.4.694L20.42 51.01c-.044.025-.092.041-.14.058-.018.006-.035.017-.054.022a.805.805 0 0 1-.41 0c-.022-.006-.042-.018-.063-.026-.044-.016-.09-.03-.132-.054L.402 39.944A.801.801 0 0 1 0 39.25V6.334c0-.072.01-.142.028-.21.006-.023.02-.044.028-.067.015-.042.029-.085.051-.124.015-.026.037-.047.055-.071.023-.032.044-.065.071-.093.023-.023.053-.04.079-.06.029-.024.055-.05.088-.069h.001l9.61-5.533a.802.802 0 0 1 .8 0l9.61 5.533h.002c.032.02.059.045.088.068.026.02.055.038.078.06.028.029.048.062.072.094.017.024.04.045.054.071.023.04.036.082.052.124.008.023.022.044.028.068a.809.809 0 0 1 .028.209v20.559l8.008-4.611v-10.51c0-.07.01-.141.028-.208.007-.024.02-.045.028-.068.016-.042.03-.085.052-.124.015-.026.037-.047.054-.071.024-.032.044-.065.072-.093.023-.023.052-.04.078-.06.03-.024.056-.05.088-.069h.001l9.611-5.533a.801.801 0 0 1 .8 0l9.61 5.533c.034.02.06.045.09.068.025.02.054.038.077.06.028.029.048.062.072.094.018.024.04.045.054.071.023.039.036.082.052.124.009.023.022.044.028.068zm-1.574 10.718v-9.124l-3.363 1.936-4.646 2.675v9.124l8.01-4.611zm-9.61 16.505v-9.13l-4.57 2.61-13.05 7.448v9.216l17.62-10.144zM1.602 7.719v31.068L19.22 48.93v-9.214l-9.204-5.209-.003-.002-.004-.002c-.031-.018-.057-.044-.086-.066-.025-.02-.054-.036-.076-.058l-.002-.003c-.026-.025-.044-.056-.066-.084-.02-.027-.044-.05-.06-.078l-.001-.003c-.018-.03-.029-.066-.042-.1-.013-.03-.03-.058-.038-.09v-.001c-.01-.038-.012-.078-.016-.117-.004-.03-.012-.06-.012-.09v-.002-21.481L4.965 9.654 1.602 7.72zm8.81-5.994L2.405 6.334l8.005 4.609 8.006-4.61-8.006-4.608zm4.164 28.764l4.645-2.674V7.719l-3.363 1.936-4.646 2.675v20.096l3.364-1.937zM39.243 7.164l-8.006 4.609 8.006 4.609 8.005-4.61-8.005-4.608zm-.801 10.605l-4.646-2.675-3.363-1.936v9.124l4.645 2.674 3.364 1.937v-9.124zM20.02 38.33l11.743-6.704 5.87-3.35-8-4.606-9.211 5.303-8.395 4.833 7.993 4.524z" fill="currentColor" />
                    </svg>
                </div>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h2>Create an account</h2>
                <p>Enter your details below to create your account</p>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register.submit') }}">
                @csrf
                <input type="hidden" name="account_type" value="organization">

                <div class="form-group">
                    <label for="organization_name" class="form-label">Organization Name *</label>
                    <input type="text" id="organization_name" name="organization_name" class="form-input @error('organization_name') is-invalid @enderror" value="{{ old('organization_name') }}" required autocomplete="organization" placeholder="Acme Inc.">
                    @error('organization_name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                    <p style="font-size: 0.8rem; color: #6b7280; margin-top: 0.4rem;">
                        We'll create this organization and make you its first org admin immediately.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Starting Plan</label>
                    @if($freePlan)
                    <div style="border: 2px solid #6366f1; border-radius: 8px; padding: 1rem; background: #f5f3ff;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #4f46e5; font-size: 1rem;">{{ $freePlan->name }} Plan</span>
                            <span style="background: #4f46e5; color: #fff; font-size: 0.75rem; font-weight: 600; padding: 2px 10px; border-radius: 999px;">Free</span>
                        </div>
                        @if($freePlan->description)
                        <p style="font-size: 0.85rem; color: #6b7280; margin: 0 0 0.5rem;">{{ $freePlan->description }}</p>
                        @endif
                        <ul style="font-size: 0.82rem; color: #374151; margin: 0; padding-left: 1.1rem; line-height: 1.7;">
                            <li>Up to {{ $freePlan->max_users ?? 'Unlimited' }} users</li>
                            <li>{{ $freePlan->max_meeting_duration ? $freePlan->max_meeting_duration . ' min' : 'Unlimited' }} per meeting</li>
                            <li>{{ $freePlan->concurrent_meetings ?? 'Unlimited' }} concurrent meeting(s)</li>
                            <li>{{ $freePlan->recording_storage_gb ? $freePlan->recording_storage_gb . ' GB' : 'No' }} recording storage</li>
                        </ul>
                    </div>
                    @else
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.75rem 1rem; background: #f9fafb;">
                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">The default organization plan will be applied after setup.</p>
                    </div>
                    @endif
                </div>

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">Your Name *</label>
                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="John Doe">
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" placeholder="email@example.com">
                    @error('email')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" class="form-input @error('password') is-invalid @enderror" required autocomplete="new-password" placeholder="Password" minlength="8">
                    @error('password')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password" placeholder="Confirm Password">
                    @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                    Create account
                </button>
            </form>

            <!-- Login Link -->
            <div class="form-footer">
                <p>
                    Already have an account?
                    <a href="{{ route('tyro-login.login') }}" class="form-link">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
