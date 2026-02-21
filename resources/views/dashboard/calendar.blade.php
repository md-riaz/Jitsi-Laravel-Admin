@extends('tyro-dashboard::layouts.app')

@section('title', 'Calendar')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Calendar</span>
@endsection

@push('styles')
@vite('resources/css/calendar.css')
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Meeting Calendar</h1>
            <p class="page-description" style="font-size: 1rem;">View all your meetings in calendar format.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('dashboard.my-meetings') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                List View
            </a>
            <a href="{{ route('dashboard.create-meeting') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; margin-right: 8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Meeting
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Pass the calendar events URL to the calendar.js module
    window.calendarEventsUrl = '{{ route('dashboard.calendar.events') }}';
</script>
@vite('resources/js/calendar.js')
@endpush
