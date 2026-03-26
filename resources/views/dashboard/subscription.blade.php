@extends('tyro-dashboard::layouts.app')

@section('title', 'My Subscription')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>My Subscription</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">My Subscription</h1>
    <p class="page-subtitle">
        @if($isOrgPlan)
            Your account is part of an organization. The subscription plan below is managed by your organization admin and the Super Admin.
        @else
            Your personal subscription plan. To request an upgrade, contact our sales team.
        @endif
    </p>
</div>

@if($plan)
<div style="max-width: 680px;">
    <!-- Current Plan Card -->
    <div style="border: 2px solid #6366f1; border-radius: 12px; padding: 1.75rem 2rem; background: #f5f3ff; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div>
                <h2 style="margin: 0 0 0.25rem; font-size: 1.5rem; color: #4f46e5; font-weight: 700;">{{ $plan->name }} Plan</h2>
                @if($plan->description)
                    <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">{{ $plan->description }}</p>
                @endif
            </div>
            <div style="text-align: right;">
                <span style="font-size: 1.75rem; font-weight: 800; color: #111827;">
                    ${{ number_format($plan->price, 2) }}
                </span>
                <span style="font-size: 0.85rem; color: #6b7280;">/ {{ $plan->billing_cycle }}</span>
            </div>
        </div>

        <!-- Plan Limits -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div style="background: #fff; border-radius: 8px; padding: 0.75rem 1rem; text-align: center; border: 1px solid #e0e7ff;">
                <div style="font-size: 1.25rem; font-weight: 700; color: #4f46e5;">
                    {{ $plan->max_users ?? '∞' }}
                </div>
                <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">Max Users</div>
            </div>
            <div style="background: #fff; border-radius: 8px; padding: 0.75rem 1rem; text-align: center; border: 1px solid #e0e7ff;">
                <div style="font-size: 1.25rem; font-weight: 700; color: #4f46e5;">
                    {{ $plan->max_meeting_duration ? $plan->max_meeting_duration . ' min' : '∞' }}
                </div>
                <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">Meeting Duration</div>
            </div>
            <div style="background: #fff; border-radius: 8px; padding: 0.75rem 1rem; text-align: center; border: 1px solid #e0e7ff;">
                <div style="font-size: 1.25rem; font-weight: 700; color: #4f46e5;">
                    {{ $plan->concurrent_meetings ?? '∞' }}
                </div>
                <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">Concurrent Meetings</div>
            </div>
            <div style="background: #fff; border-radius: 8px; padding: 0.75rem 1rem; text-align: center; border: 1px solid #e0e7ff;">
                <div style="font-size: 1.25rem; font-weight: 700; color: #4f46e5;">
                    {{ $plan->recording_storage_gb !== null ? $plan->recording_storage_gb . ' GB' : '∞' }}
                </div>
                <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">Recording Storage</div>
            </div>
        </div>

        @if($isOrgPlan && $org)
        <!-- Org subscription dates -->
        <div style="background: #fff; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #e0e7ff; margin-bottom: 0.5rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
                @if($org->subscription_starts_at)
                <div>
                    <span style="font-size: 0.78rem; color: #6b7280; display: block;">Start Date</span>
                    <span style="font-weight: 600; color: #111827;">{{ $org->subscription_starts_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if($org->subscription_ends_at)
                <div>
                    <span style="font-size: 0.78rem; color: #6b7280; display: block;">Expiry Date</span>
                    <span style="font-weight: 600; color: {{ $org->isSubscriptionExpired() ? '#ef4444' : '#111827' }};">
                        {{ $org->subscription_ends_at->format('M d, Y') }}
                        @if($org->isSubscriptionExpired())
                            <span style="font-size: 0.75rem; color: #ef4444;">(Expired)</span>
                        @elseif($org->isSubscriptionExpiringSoon())
                            <span style="font-size: 0.75rem; color: #f59e0b;">(Expiring soon)</span>
                        @endif
                    </span>
                </div>
                @endif
                @if($org->subscription_status)
                <div>
                    <span style="font-size: 0.78rem; color: #6b7280; display: block;">Status</span>
                    <span style="font-weight: 600; text-transform: capitalize; color: #111827;">{{ $org->subscription_status }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Upgrade Notice -->
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1.25rem 1.5rem;">
        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <svg style="flex-shrink:0; color: #f59e0b; width: 22px; height: 22px; margin-top: 1px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p style="margin: 0 0 0.4rem; font-weight: 600; color: #92400e;">Want to upgrade your plan?</p>
                <p style="margin: 0; font-size: 0.875rem; color: #78350f;">
                    @if($isOrgPlan)
                        Plan changes for your organization are handled by the Super Admin.
                        Please contact your organization admin or the platform support team to request an upgrade.
                    @else
                        To upgrade from the Free Plan to a higher tier, please contact our sales team.
                        We'll set up your new plan and billing arrangements.
                    @endif
                </p>
                <a href="mailto:sales@example.com"
                   style="display: inline-block; margin-top: 0.75rem; padding: 0.5rem 1.25rem; background: #f59e0b; color: #fff; border-radius: 6px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                    Contact Sales
                </a>
            </div>
        </div>
    </div>
</div>
@else
<div style="max-width: 680px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 2rem; text-align: center;">
    <p style="color: #6b7280; margin: 0 0 1rem;">No subscription plan is currently assigned to your account.</p>
    <a href="mailto:sales@example.com" style="color: #4f46e5; font-weight: 600;">Contact sales to get started →</a>
</div>
@endif
@endsection
