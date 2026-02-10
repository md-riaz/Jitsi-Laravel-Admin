<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} - Jitsi Meeting</title>
    <script src="https://{{ config('services.jitsi.domain') }}/external_api.js" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 32px;
        }
        .header h1 {
            font-size: 1.875rem;
            color: #1e293b;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .header .info {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .header .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.9375rem;
        }
        .header .info-item svg {
            width: 20px;
            height: 20px;
            color: #94a3b8;
        }
        .content {
            padding: 32px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .status-live {
            background: #dcfce7;
            color: #15803d;
        }
        .status-live::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .status-upcoming {
            background: #fef3c7;
            color: #92400e;
        }
        .status-ended {
            background: #f1f5f9;
            color: #475569;
        }
        .countdown {
            text-align: center;
            padding: 48px;
            background: #f8fafc;
            border-radius: 12px;
            margin: 24px 0;
        }
        .countdown h2 {
            color: #334155;
            margin-bottom: 24px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .countdown-timer {
            font-size: 3.5rem;
            font-weight: 700;
            color: #667eea;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }
        .join-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #667eea;
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
        }
        .join-button:hover:not(:disabled) {
            background: #5a67d8;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transform: translateY(-1px);
        }
        .join-button:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            box-shadow: none;
        }
        #jitsi-container {
            width: 100%;
            height: 600px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 24px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .meeting-details {
            background: #f8fafc;
            padding: 24px;
            border-radius: 12px;
            margin-top: 24px;
        }
        .meeting-details h3 {
            margin-bottom: 12px;
            color: #334155;
            font-size: 1.125rem;
            font-weight: 600;
        }
        .meeting-details p {
            color: #64748b;
            line-height: 1.6;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin: 24px 0;
            display: flex;
            align-items: start;
            gap: 12px;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .alert strong {
            font-weight: 600;
        }
        .instant-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #f0fdf4;
            color: #15803d;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                {{ $meeting->title }}
                @if($meeting->isInstantMeeting())
                    <span class="instant-badge">⚡ Instant Meeting</span>
                @endif
            </h1>
            <div class="info">
                @if(!$meeting->isInstantMeeting())
                    <div class="info-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $meeting->start_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }} ({{ $meeting->timezone }})</span>
                    </div>
                @endif
                <div class="info-item">
                    @if($status === 'live')
                        <span class="status-badge status-live">Live Now</span>
                    @elseif($status === 'not_started')
                        <span class="status-badge status-upcoming">Upcoming</span>
                    @else
                        <span class="status-badge status-ended">Ended</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="content">
            @if($status === 'not_started')
                <div class="countdown">
                    <h2>Meeting starts in</h2>
                    <div class="countdown-timer" id="countdown-timer">Loading...</div>
                    <p style="color: #64748b; margin-top: 16px; font-size: 0.9375rem;">You can join {{ $meeting->join_early_minutes }} minutes before the scheduled time</p>
                </div>
                <div style="text-align: center;">
                    <button class="join-button" id="join-button" disabled>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Join Meeting
                    </button>
                </div>
            @elseif($status === 'live')
                <div class="alert alert-info">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong>Meeting is live!</strong> Click the button below to join.
                    </div>
                </div>
                <div style="text-align: center; margin: 24px 0;">
                    <button class="join-button" id="join-button" onclick="joinMeeting()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Join Meeting Now
                    </button>
                </div>
                <div id="jitsi-container" style="display: none;"></div>
            @else
                <div class="alert alert-error">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink: 0;">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong>Meeting has ended.</strong> This meeting is no longer available.
                    </div>
                </div>
            @endif

            @if($meeting->description)
                <div class="meeting-details">
                    <h3>About this meeting</h3>
                    <p>{{ $meeting->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        const meetingId = '{{ $meeting->id }}';
        const status = '{{ $status }}';
        @if(!$meeting->isInstantMeeting())
        const opensAt = new Date('{{ $opensAt->toIso8601String() }}');
        const closesAt = new Date('{{ $closesAt->toIso8601String() }}');
        @endif
        let jitsiApi = null;

        @if(!$meeting->isInstantMeeting())
        function updateCountdown() {
            const now = new Date();
            const diff = opensAt - now;
            
            if (diff <= 0) {
                document.getElementById('countdown-timer').textContent = 'You can join now!';
                document.getElementById('join-button').disabled = false;
                clearInterval(countdownInterval);
                location.reload();
                return;
            }

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('countdown-timer').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        if (status === 'not_started') {
            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        }
        @endif

        async function joinMeeting() {
            // Check if Jitsi API is loaded
            if (typeof JitsiMeetExternalAPI === 'undefined') {
                alert('Jitsi Meet API failed to load. Please check your internet connection and try again.');
                console.error('JitsiMeetExternalAPI is not defined. Script may have failed to load from {{ config("services.jitsi.domain") }}');
                return;
            }

            const button = document.getElementById('join-button');
            button.disabled = true;
            button.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" stroke-opacity="0.25"></circle><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path></svg> Joining...';

            try {
                const response = await fetch(`/api/meetings/${meetingId}/join`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!data.can_join) {
                    alert(data.message || 'Cannot join meeting at this time');
                    button.disabled = false;
                    button.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Join Meeting Now';
                    return;
                }

                button.style.display = 'none';
                document.getElementById('jitsi-container').style.display = 'block';

                const domain = data.domain;
                const options = {
                    roomName: data.room_name,
                    width: '100%',
                    height: '600px',
                    parentNode: document.querySelector('#jitsi-container'),
                    userInfo: {
                        displayName: data.display_name || 'Guest'
                    },
                    configOverwrite: {
                        prejoinPageEnabled: false,
                        startWithAudioMuted: true,
                        startWithVideoMuted: true
                    },
                    interfaceConfigOverwrite: {
                        SHOW_JITSI_WATERMARK: false,
                        DEFAULT_REMOTE_DISPLAY_NAME: 'Participant'
                    }
                };

                if (data.jwt) {
                    options.jwt = data.jwt;
                }

                jitsiApi = new JitsiMeetExternalAPI(domain, options);

            } catch (error) {
                console.error('Error joining meeting:', error);
                alert('Failed to join meeting. Please try again.');
                button.disabled = false;
                button.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Join Meeting Now';
            }
        }
    </script>
</body>
</html>
