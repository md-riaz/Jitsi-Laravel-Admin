@extends('tyro-dashboard::layouts.app')

@section('title', 'Meeting Summary')

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
  <div>
    <h1 class="page-title">Meeting Summary</h1>
    <p class="page-description">{{ $meeting->title }} ({{ $meeting->id }})</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a class="btn btn-secondary" href="{{ route('dashboard.meetings.diagnostics', $meeting->id) }}">Diagnostics</a>
    <a class="btn btn-secondary" href="{{ route('dashboard.meetings.summary.export.participants', $meeting->id) }}">Export Attendance CSV</a>
    <a class="btn btn-secondary" href="{{ route('dashboard.meetings.summary.export.events', $meeting->id) }}">Export Events CSV</a>
    <a class="btn btn-primary" target="_blank" href="{{ route('meeting.show', $meeting->id) }}">Open Meeting Page</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:14px;">
  <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Unique participants</div><div style="font-size:24px;font-weight:700;">{{ $kpis['unique_participants'] }}</div></div></div>
  <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Peak participants</div><div style="font-size:24px;font-weight:700;">{{ $kpis['peak_participants'] }}</div></div></div>
  <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Join events</div><div style="font-size:24px;font-weight:700;">{{ $kpis['join_events'] }}</div></div></div>
  <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Leave events</div><div style="font-size:24px;font-weight:700;">{{ $kpis['leave_events'] }}</div></div></div>
  <div class="card"><div class="card-body"><div style="font-size:12px;color:#64748b;">Duration (min)</div><div style="font-size:24px;font-weight:700;">{{ $kpis['duration_minutes'] ?? '—' }}</div></div></div>
</div>

<div class="card" style="margin-bottom:14px;">
  <div class="card-header"><h3>Attendance</h3></div>
  <div class="card-body" style="overflow:auto;">
    <table class="table" style="width:100%;">
      <thead><tr><th>Name</th><th>Identity</th><th>Joined</th><th>Left</th><th>Duration (min)</th></tr></thead>
      <tbody>
        @forelse($attendanceRows as $r)
          <tr>
            <td>{{ $r['name'] }}</td>
            <td>{{ $r['identity'] }}</td>
            <td>{{ $r['joined_at'] ?? '—' }}</td>
            <td>{{ $r['left_at'] ?? '—' }}</td>
            <td>{{ $r['duration_minutes'] ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:#64748b;">No attendance data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3>Event Timeline</h3></div>
  <div class="card-body">
    @if($timeline->isEmpty())
      <p style="color:#64748b;">No timeline events.</p>
    @else
      <div style="display:grid;gap:8px;">
        @foreach($timeline as $t)
          <div style="padding:10px;border:1px solid #e2e8f0;border-radius:8px;">
            <div style="display:flex;justify-content:space-between;gap:12px;">
              <strong>{{ $t['type'] }}</strong>
              <span style="color:#64748b;">{{ $t['time'] }}</span>
            </div>
            <code style="display:block;white-space:pre-wrap;margin-top:6px;">{{ json_encode($t['payload'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</code>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection
