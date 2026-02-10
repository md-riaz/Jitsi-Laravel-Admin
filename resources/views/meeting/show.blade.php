<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} - Jitsi Meeting</title>
    <script src="https://{{ config('services.jitsi.domain') }}/external_api.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .header .info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
            opacity: 0.95;
        }
        .header .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
        }
        .status-live {
            background: #10b981;
            color: white;
        }
        .status-upcoming {
            background: #f59e0b;
            color: white;
        }
        .status-ended {
            background: #6b7280;
            color: white;
        }
        .countdown {
            text-align: center;
            padding: 40px;
            background: #f9fafb;
            border-radius: 8px;
            margin: 20px 0;
        }
        .countdown h2 {
            color: #374151;
            margin-bottom: 20px;
        }
        .countdown-timer {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            font-variant-numeric: tabular-nums;
        }
        .join-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .join-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .join-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        #jitsi-container {
            width: 100%;
            height: 600px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        .meeting-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .meeting-details h3 {
            margin-bottom: 10px;
            color: #374151;
        }
        .meeting-details p {
            color: #6b7280;
            line-height: 1.6;
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $meeting->title }}</h1>
            <div class="info">
                <div class="info-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $meeting->start_at->format('M d, Y') }}</span>
                </div>
                <div class="info-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }} ({{ $meeting->timezone }})</span>
                </div>
                <div class="info-item">
                    @if($status === 'live')
                        <span class="status-badge status-live">● Live Now</span>
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
                    <p style="color: #6b7280; margin-top: 15px;">You can join {{ $meeting->join_early_minutes }} minutes before the scheduled time</p>
                </div>
                <div style="text-align: center;">
                    <button class="join-button" id="join-button" disabled>Join Meeting</button>
                </div>
            @elseif($status === 'live')
                <div class="alert alert-info">
                    <strong>Meeting is live!</strong> Click the button below to join.
                </div>
                <div style="text-align: center; margin: 20px 0;">
                    <button class="join-button" id="join-button" onclick="joinMeeting()">Join Meeting Now</button>
                </div>
                <div id="jitsi-container" style="display: none;"></div>
            @else
                <div class="alert alert-error">
                    <strong>Meeting has ended.</strong> This meeting is no longer available.
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
        const opensAt = new Date('{{ $opensAt->toIso8601String() }}');
        const closesAt = new Date('{{ $closesAt->toIso8601String() }}');
        let jitsiApi = null;

        function updateCountdown() {
            const now = new Date();
            const diff = opensAt - now;
            
            if (diff <= 0) {
                // Meeting can be joined now
                document.getElementById('countdown-timer').textContent = 'You can join now!';
                document.getElementById('join-button').disabled = false;
                clearInterval(countdownInterval);
                location.reload(); // Reload to show join button
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

        async function joinMeeting() {
            const button = document.getElementById('join-button');
            button.disabled = true;
            button.textContent = 'Joining...';

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
                    button.textContent = 'Join Meeting Now';
                    return;
                }

                // Hide join button and show Jitsi container
                button.style.display = 'none';
                document.getElementById('jitsi-container').style.display = 'block';

                // Initialize Jitsi
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

                // Add JWT if available
                if (data.jwt) {
                    options.jwt = data.jwt;
                }

                jitsiApi = new JitsiMeetExternalAPI(domain, options);

            } catch (error) {
                console.error('Error joining meeting:', error);
                alert('Failed to join meeting. Please try again.');
                button.disabled = false;
                button.textContent = 'Join Meeting Now';
            }
        }
    </script>
</body>
</html>
