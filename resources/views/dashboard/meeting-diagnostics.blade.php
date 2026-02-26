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
  <div class="card-header"><h3>JWT / Jitsi Config Diagnostics</h3></div>
  <div class="card-body">
    <ul style="line-height:1.9; margin:0; padding-left:18px;">
      <li><strong>Domain:</strong> {{ $jwtConfig['domain'] ?: '—' }}</li>
      <li><strong>Issuer:</strong> {{ $jwtConfig['issuer'] ?: '—' }}</li>
      <li><strong>Audience:</strong> {{ $jwtConfig['audience'] ?: '—' }}</li>
      <li><strong>SUB:</strong> {{ $jwtConfig['sub'] ?: '—' }}</li>
      <li><strong>Secret configured:</strong> {{ $jwtConfig['has_secret'] ? 'Yes' : 'No' }}</li>
      <li><strong>Org JWT expiry:</strong> {{ $jwtConfig['org_expiry_minutes'] }} min</li>
    </ul>

    <div style="margin-top:12px; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:8px;">
      <div class="badge {{ $jwtChecks['secret_configured'] ? 'badge-success' : 'badge-danger' }}">Secret: {{ $jwtChecks['secret_configured'] ? 'OK' : 'Missing' }}</div>
      <div class="badge {{ $jwtChecks['issuer_present'] ? 'badge-success' : 'badge-danger' }}">Issuer: {{ $jwtChecks['issuer_present'] ? 'OK' : 'Missing' }}</div>
      <div class="badge {{ $jwtChecks['audience_present'] ? 'badge-success' : 'badge-danger' }}">Audience: {{ $jwtChecks['audience_present'] ? 'OK' : 'Missing' }}</div>
      <div class="badge {{ $jwtChecks['sub_present'] ? 'badge-success' : 'badge-danger' }}">SUB: {{ $jwtChecks['sub_present'] ? 'OK' : 'Missing' }}</div>
      <div class="badge {{ $jwtChecks['sub_matches_domain'] ? 'badge-success' : 'badge-warning' }}">SUB matches Domain: {{ $jwtChecks['sub_matches_domain'] ? 'Yes' : 'No' }}</div>
    </div>

    <div style="margin-top:14px;">
      <strong>JWT test token generation:</strong>
      @if($testTokenError)
        <div style="color:#b91c1c; margin-top:6px;">Error: {{ $testTokenError }}</div>
      @elseif(!$testToken)
        <div style="color:#92400e; margin-top:6px;">Token not generated (likely JWT not required/configured for this meeting).</div>
      @else
        <div style="color:#166534; margin-top:6px;">Token generated successfully.</div>
        @if($testClaims)
          <pre style="margin-top:8px; background:#0f172a; color:#e2e8f0; padding:10px; border-radius:8px; white-space:pre-wrap;">{{ json_encode($testClaims, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
      @endif
    </div>
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
