@extends('tyro-dashboard::layouts.app')

@section('title', 'My Subscription')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>My Subscription</span>
@endsection

@section('content')
<style>
.subscription-wrap {
    max-width: 680px;
}

.subscription-card {
    border: 2px solid color-mix(in srgb, var(--primary), transparent 20%);
    border-radius: 12px;
    padding: 1.75rem 2rem;
    background: color-mix(in srgb, var(--primary), transparent 94%);
    margin-bottom: 1.5rem;
}

.subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.subscription-plan-title {
    margin: 0 0 0.25rem;
    font-size: 1.5rem;
    color: color-mix(in srgb, var(--primary), black 18%);
    font-weight: 700;
}

.subscription-desc {
    margin: 0;
    color: var(--muted-foreground);
    font-size: 0.9rem;
}

.subscription-price-wrap {
    text-align: right;
}

.subscription-price {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--foreground);
}

.subscription-cycle {
    font-size: 0.85rem;
    color: var(--muted-foreground);
}

.subscription-limits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.subscription-limit-card {
    background: var(--background);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    text-align: center;
    border: 1px solid color-mix(in srgb, var(--primary), transparent 80%);
}

.subscription-limit-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: color-mix(in srgb, var(--primary), black 20%);
}

.subscription-limit-label {
    font-size: 0.78rem;
    color: var(--muted-foreground);
    margin-top: 2px;
}

.subscription-org-meta {
    background: var(--background);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    border: 1px solid color-mix(in srgb, var(--primary), transparent 80%);
    margin-bottom: 0.5rem;
}

.subscription-org-meta-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.subscription-org-meta-label {
    font-size: 0.78rem;
    color: var(--muted-foreground);
    display: block;
}

.subscription-org-meta-value {
    font-weight: 600;
    color: var(--foreground);
}

.subscription-status-text {
    text-transform: capitalize;
}

.subscription-expired {
    color: var(--destructive);
}

.subscription-expiring {
    color: var(--warning);
}

.subscription-notice {
    background: color-mix(in srgb, var(--warning), transparent 90%);
    border: 1px solid color-mix(in srgb, var(--warning), transparent 55%);
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
}

.subscription-notice-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.subscription-notice-icon {
    flex-shrink: 0;
    color: var(--warning);
    width: 22px;
    height: 22px;
    margin-top: 1px;
}

.subscription-notice-title {
    margin: 0 0 0.4rem;
    font-weight: 600;
    color: color-mix(in srgb, var(--warning), black 40%);
}

.subscription-notice-text {
    margin: 0;
    font-size: 0.875rem;
    color: color-mix(in srgb, var(--warning), black 50%);
}

.subscription-empty {
    max-width: 680px;
    background: var(--muted);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
}

.subscription-empty p {
    color: var(--muted-foreground);
    margin: 0 0 1rem;
}

.subscription-link {
    color: color-mix(in srgb, var(--primary), black 15%);
    font-weight: 600;
    text-decoration: none;
}

.subscription-link:hover {
    text-decoration: underline;
}

.subscription-contact-btn {
    margin-top: 0.75rem;
}
</style>

<div class="page-header">
    <h1 class="page-title">My Subscription</h1>
    <p class="page-description">
        @if($isOrgPlan)
            Your account is part of an organization. The subscription plan below is managed by your organization admin and the Super Admin.
        @else
            Your personal subscription plan. To request an upgrade, contact our sales team.
        @endif
    </p>
</div>

@if($plan)
<div class="subscription-wrap">
    <div class="subscription-card">
        <div class="subscription-header">
            <div>
                <h2 class="subscription-plan-title">{{ $plan->name }} Plan</h2>
                @if($plan->description)
                    <p class="subscription-desc">{{ $plan->description }}</p>
                @endif
            </div>
            <div class="subscription-price-wrap">
                <span class="subscription-price">${{ number_format($plan->price, 2) }}</span>
                <span class="subscription-cycle">/ {{ $plan->billing_cycle }}</span>
            </div>
        </div>

        <div class="subscription-limits-grid">
            <div class="subscription-limit-card">
                <div class="subscription-limit-value">{{ $plan->max_users ?? '∞' }}</div>
                <div class="subscription-limit-label">Max Users</div>
            </div>
            <div class="subscription-limit-card">
                <div class="subscription-limit-value">{{ $plan->max_meeting_duration ? $plan->max_meeting_duration . ' min' : '∞' }}</div>
                <div class="subscription-limit-label">Meeting Duration</div>
            </div>
            <div class="subscription-limit-card">
                <div class="subscription-limit-value">{{ $plan->concurrent_meetings ?? '∞' }}</div>
                <div class="subscription-limit-label">Concurrent Meetings</div>
            </div>
            <div class="subscription-limit-card">
                <div class="subscription-limit-value">{{ $plan->recording_storage_gb !== null ? $plan->recording_storage_gb . ' GB' : '∞' }}</div>
                <div class="subscription-limit-label">Recording Storage</div>
            </div>
        </div>

        @if($isOrgPlan && $org)
        <div class="subscription-org-meta">
            <div class="subscription-org-meta-grid">
                @if($org->subscription_starts_at)
                <div>
                    <span class="subscription-org-meta-label">Start Date</span>
                    <span class="subscription-org-meta-value">{{ $org->subscription_starts_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if($org->subscription_ends_at)
                <div>
                    <span class="subscription-org-meta-label">Expiry Date</span>
                    <span class="subscription-org-meta-value {{ $org->isSubscriptionExpired() ? 'subscription-expired' : '' }}">
                        {{ $org->subscription_ends_at->format('M d, Y') }}
                        @if($org->isSubscriptionExpired())
                            <span class="subscription-expired">(Expired)</span>
                        @elseif($org->isSubscriptionExpiringSoon())
                            <span class="subscription-expiring">(Expiring soon)</span>
                        @endif
                    </span>
                </div>
                @endif
                @if($org->subscription_status)
                <div>
                    <span class="subscription-org-meta-label">Status</span>
                    <span class="subscription-org-meta-value subscription-status-text">{{ $org->subscription_status }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="subscription-notice">
        <div class="subscription-notice-row">
            <svg class="subscription-notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="subscription-notice-title">Want to upgrade your plan?</p>
                <p class="subscription-notice-text">
                    @if($isOrgPlan)
                        Plan changes for your organization are handled by the Super Admin.
                        Please contact your organization admin or the platform support team to request an upgrade.
                    @else
                        To upgrade from the Free Plan to a higher tier, please contact our sales team.
                        We'll set up your new plan and billing arrangements.
                    @endif
                </p>
                <a href="mailto:sales@example.com" class="btn btn-primary subscription-contact-btn">Contact Sales</a>
            </div>
        </div>
    </div>
</div>
@else
<div class="subscription-empty">
    <p>No subscription plan is currently assigned to your account.</p>
    <a href="mailto:sales@example.com" class="subscription-link">Contact sales to get started →</a>
</div>
@endif
@endsection
