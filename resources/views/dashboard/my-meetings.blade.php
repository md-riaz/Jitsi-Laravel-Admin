@extends('tyro-dashboard::layouts.app')

@section('title', 'My Meetings')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>My Meetings</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Meetings</h1>
            <p class="page-description" style="font-size: 1rem;">A common page accessible to all users.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">My Meetings Content</h3>
    </div>
    <div class="card-body">
        <p>This is a common dashboard page visible to both regular users and administrators. Start building your content here.</p>
    </div>
</div>
@endsection
