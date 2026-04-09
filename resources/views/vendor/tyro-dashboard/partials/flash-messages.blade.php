@if (session('success'))
<div class="alert alert-success" data-auto-dismiss="10000">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="alert-content">
        <p class="alert-message">{{ session('success') }}</p>
    </div>
</div>
@endif

@if (session('error'))
<div class="alert alert-error" data-auto-dismiss="10000">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="alert-content">
        <p class="alert-message">{{ session('error') }}</p>
    </div>
</div>
@endif

@if (session('warning'))
<div class="alert alert-warning" data-auto-dismiss="10000">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <div class="alert-content">
        <p class="alert-message">{{ session('warning') }}</p>
    </div>
</div>
@endif

@if (session('info'))
<div class="alert alert-info" data-auto-dismiss="10000">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="alert-content">
        <p class="alert-message">{{ session('info') }}</p>
    </div>
</div>
@endif

@if ($errors->any() && config('tyro-dashboard.resource_ui.show_global_errors', true))
<div class="alert alert-error" data-auto-dismiss="10000">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="alert-content">
        <p class="alert-message">{{ implode(' | ', $errors->all()) }}</p>
    </div>
</div>
@endif
