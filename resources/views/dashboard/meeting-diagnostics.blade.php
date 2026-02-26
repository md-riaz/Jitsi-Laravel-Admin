@extends('tyro-dashboard::layouts.app')

@section('title', 'Meeting Diagnostics')

@section('content')
<div class="page-header">
  <h1 class="page-title">Meeting Diagnostics</h1>
  <p class="page-description">{{ $meeting->title }} ({{ $meeting->id }})</p>
</div>

<div class="card" style="margin-bottom:16px;">
  <div class="card-body">
    <ul style="line-height:1.9; margin:0; padding-left:18px;">
      <li><strong>Visibility:</strong> {{ $visibility }}</li>
      <li><strong>Allow guests (computed):</strong> {{ $meeting->allow_guests ? 'Yes' : 'No' }}</li>
      <li><strong>Lobby enabled:</strong> {{ $meeting->lobby_enabled ? 'Yes' : 'No' }}</li>
      <li><strong>Org requires JWT:</strong> {{ optional($meeting->organization)->require_jwt ? 'Yes' : 'No' }}</li>
      <li><strong>Status:</strong> {{ $meeting->status }}</li>
    </ul>
  </div>
</div>

<div class="card" style="margin-bottom:16px;">
  <div class="card-header"><h3>Pending Admissions</h3></div>
  <div class="card-body">
    @if($pendingAdmissions->isEmpty())
      <p style="color:#6b7280;">No pending admissions.</p>
    @else
      <table class="table" style="width:100%">
        <thead><tr><th>Name</th><th>Identity</th><th>Requested At</th></tr></thead>
        <tbody>
          @foreach($pendingAdmissions as $p)
            <tr>
              <td>{{ $p->display_name }}</td>
              <td>{{ $p->email }}</td>
              <td>{{ $p->updated_at }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Recent Admission / Denial Events</h3></div>
  <div class="card-body">
    @if($recentDenials->isEmpty())
      <p style="color:#6b7280;">No recent events.</p>
    @else
      <table class="table" style="width:100%">
        <thead><tr><th>Type</th><th>Payload</th><th>Time</th></tr></thead>
        <tbody>
          @foreach($recentDenials as $e)
            <tr>
              <td>{{ $e->type }}</td>
              <td><code style="white-space:pre-wrap">{{ json_encode($e->payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</code></td>
              <td>{{ $e->created_at }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
