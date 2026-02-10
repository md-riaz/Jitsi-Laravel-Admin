@extends('tyro-dashboard::layouts.admin')

@section('title', 'System Usage')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>System Usage</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">System Usage</h1>
            <p class="page-description" style="font-size: 1rem;">Administrative page for System Usage.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">System Usage Management</h3>
    </div>
    <div class="card-body">
        <p>This is a new admin dashboard page. Start building your administrative content here.</p>
    </div>
</div>
@endsection
