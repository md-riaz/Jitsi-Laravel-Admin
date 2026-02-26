<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} - Live Meeting</title>
    <script src="https://{{ config('services.jitsi.domain') }}/external_api.js" defer></script>
    <style>
        html, body { margin:0; padding:0; width:100%; height:100%; background:#0b0f19; color:#fff; font-family:Inter,system-ui,sans-serif; }
        .stage { position:fixed; inset:0; display:flex; align-items:center; justify-content:center; }
        .panel { text-align:center; max-width:520px; padding:24px; }
        .btn { background:#3b82f6; color:#fff; border:0; border-radius:10px; padding:12px 18px; font-weight:600; cursor:pointer; }
        .btn:disabled { opacity:.5; cursor:not-allowed; }
        #jitsi-container { position:fixed; inset:0; width:100vw; height:100vh; display:none; }
        #jitsi-container iframe { width:100vw !important; height:100vh !important; border:0; }
        .muted { color:#9fb1d1; margin-top:10px; }
    </style>
</head>
<body>
<div class="stage" id="entry">
    <div class="panel">
        <h2>{{ $meeting->title }}</h2>
        @if($status === 'ended')
            <p>This meeting is not available right now.</p>
        @else
            <button class="btn" id="joinBtn">Join meeting</button>
            <div class="muted" id="statusText">Preparing secure join…</div>
        @endif
    </div>
</div>
<div id="jitsi-container"></div>
<script>
window.MEETING_BOOTSTRAP = {
  meetingId: @json($meeting->id),
  status: @json($status),
  apiBase: @json(url('/api')),
  dashboardUrl: @json(route('tyro-dashboard.index'))
};
</script>
<script src="{{ asset('js/meeting-live.js') }}"></script>
</body>
</html>
