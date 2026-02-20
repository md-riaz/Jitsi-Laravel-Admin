<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} - Jitsi Admin</title>
    <link rel="preconnect" href="https://{{ config('services.jitsi.domain') }}">
    <script src="https://{{ config('services.jitsi.domain') }}/external_api.js" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            padding: 0;
            color: var(--gray-900);
            line-height: 1.6;
        }

        /* Header */
        .top-header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--gray-900);
            font-weight: 600;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            font-weight: bold;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-secondary {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            color: var(--gray-700);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem 2rem;
        }

        .meeting-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Meeting Header */
        .meeting-header {
            background: white;
            color: var(--gray-900);
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--gray-200);
        }

        .meeting-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 1;
        }

        .meeting-header-content {
            position: relative;
            z-index: 1;
        }

        .meeting-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .meeting-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9375rem;
            opacity: 0.95;
        }

        .meta-item svg {
            width: 20px;
            height: 20px;
            opacity: 0.9;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.025em;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
        }

        .status-live {
            background: #ecfdf5;
            border-color: #86efac;
            color: #166534;
        }

        .status-live::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        .status-upcoming {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e;
        }

        .status-ended {
            background: var(--gray-100);
            border-color: var(--gray-300);
            color: var(--gray-700);
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.1); }
        }

        .instant-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 0.75rem;
        }

        /* Meeting Content */
        .meeting-content {
            padding: 2rem;
        }

        /* Countdown Section */
        .countdown-section {
            text-align: center;
            padding: 3rem 2rem;
            background: #eff6ff;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #dbeafe;
        }

        .countdown-title {
            font-size: 1.25rem;
            color: var(--gray-700);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .countdown-timer {
            font-size: 4rem;
            font-weight: 700;
            color: var(--primary);
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
        }

        .countdown-hint {
            color: var(--gray-600);
            font-size: 0.9375rem;
        }

        /* Alert */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
            border: 1px solid;
        }

        .alert svg {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
        }

        .alert-info {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert strong {
            font-weight: 600;
            display: block;
            margin-bottom: 0.25rem;
        }

        /* Join Button */
        .join-button-container {
            text-align: center;
            margin: 2rem 0;
        }

        .join-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: var(--primary);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-size: 1.125rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
            position: relative;
            overflow: hidden;
        }

        .join-button:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .join-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .join-button:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            box-shadow: none;
        }

        .join-button svg {
            width: 24px;
            height: 24px;
        }

        /* Jitsi Container */
        #jitsi-container {
            width: 100%;
            height: 650px;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 2rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border: 1px solid var(--gray-200);
        }

        /* Meeting Details */
        .meeting-details {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            border: 1px solid var(--gray-200);
        }

        .meeting-details h3 {
            font-size: 1.125rem;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .meeting-details p {
            color: var(--gray-700);
            line-height: 1.7;
        }

        /* Calendar Button */
        .calendar-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            color: var(--gray-700);
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .calendar-button:hover {
            background: var(--gray-50);
            border-color: var(--primary);
            color: var(--primary);
        }

        .calendar-button svg {
            width: 18px;
            height: 18px;
        }

        /* Footer */
        .meeting-footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 2rem;
            text-align: center;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--gray-600);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        /* Loading Spinner */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
                margin: 1rem auto;
            }

            .meeting-header {
                padding: 2rem 1.5rem;
            }

            .meeting-title {
                font-size: 1.5rem;
            }

            .meeting-content {
                padding: 1.5rem;
            }

            .countdown-timer {
                font-size: 3rem;
            }

            .join-button {
                width: 100%;
                padding: 1rem;
            }

            #jitsi-container {
                height: 500px;
            }

            .header-actions .btn-secondary {
                font-size: 0;
                padding: 0.5rem;
            }

            .header-actions .btn-secondary svg {
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .meeting-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .instant-badge {
                margin-left: 0;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="header-container">
            <a href="/" class="logo">
                <div class="logo-icon">J</div>
                <span>Jitsi Admin</span>
            </a>
            <div class="header-actions">
                @auth
                    <a href="{{ route('tyro-dashboard.index') }}" class="btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Sign In</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">
        <div class="meeting-card">
            <!-- Meeting Header -->
            <div class="meeting-header">
                <div class="meeting-header-content">
                    <h1 class="meeting-title">
                        {{ $meeting->title }}
                        @if($meeting->isInstantMeeting())
                            <span class="instant-badge">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"></path>
                                </svg>
                                Instant Meeting
                            </span>
                        @endif
                    </h1>
                    <div class="meeting-meta">
                        @if(!$meeting->isInstantMeeting())
                            <div class="meta-item">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $meeting->start_at->format('M d, Y') }}</span>
                            </div>
                            <div class="meta-item">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }}</span>
                            </div>
                            <div class="meta-item">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $meeting->timezone }}</span>
                            </div>
                        @endif
                        <div class="meta-item">
                            @if($status === 'live')
                                <span class="status-badge status-live">Live Now</span>
                            @elseif($status === 'not_started')
                                <span class="status-badge status-upcoming">Upcoming</span>
                            @else
                                <span class="status-badge status-ended">Ended</span>
                            @endif
                        </div>
                    </div>
                    @if(!$meeting->isInstantMeeting())
                        <a href="{{ route('meeting.download-ics', $meeting->id) }}" class="calendar-button">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Add to Calendar
                        </a>
                    @endif
                </div>
            </div>

            <!-- Meeting Content -->
            <div class="meeting-content">
                @if($status === 'not_started')
                    <div class="countdown-section">
                        <h2 class="countdown-title">Meeting starts in</h2>
                        <div class="countdown-timer" id="countdown-timer">Loading...</div>
                        <p class="countdown-hint">You can join {{ $meeting->join_early_minutes }} minutes before the scheduled time</p>
                    </div>
                    <div class="join-button-container">
                        <button class="join-button" id="join-button" disabled>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Join Meeting
                        </button>
                    </div>
                @elseif($status === 'live')
                    <div class="alert alert-success">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <strong>Meeting is live!</strong>
                            <p>Click the button below to join the meeting now.</p>
                        </div>
                    </div>
                    <div class="join-button-container">
                        <button class="join-button" id="join-button" onclick="joinMeeting()">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Join Meeting Now
                        </button>
                    </div>
                    <div id="jitsi-container" style="display: none;"></div>
                @else
                    <div class="alert alert-error">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <strong>Meeting has ended</strong>
                            <p>This meeting is no longer available to join.</p>
                        </div>
                    </div>
                @endif

                @if($meeting->description)
                    <div class="meeting-details">
                        <h3>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            About this meeting
                        </h3>
                        <p>{{ $meeting->description }}</p>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="meeting-footer">
                <p>Powered by <strong>Jitsi Admin</strong></p>
                <div class="footer-links">
                    <a href="/">Home</a>
                    @guest
                        <a href="{{ route('login') }}">Sign In</a>
                        <a href="{{ route('register') }}">Sign Up</a>
                    @endguest
                    @auth
                        <a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
                    @endauth
                </div>
            </div>
        </div>
    </main>

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
                document.getElementById('countdown-timer').textContent = 'Ready to join!';
                document.getElementById('join-button').disabled = false;
                clearInterval(countdownInterval);
                setTimeout(() => location.reload(), 1000);
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) {
                timeString = `${days}d ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            } else {
                timeString = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            document.getElementById('countdown-timer').textContent = timeString;
        }

        if (status === 'not_started') {
            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        }
        @endif

        async function joinMeeting() {
            if (typeof JitsiMeetExternalAPI === 'undefined') {
                alert('Unable to load meeting interface. Please check your internet connection and try again.');
                console.error('JitsiMeetExternalAPI not loaded from {{ config("services.jitsi.domain") }}');
                return;
            }

            const button = document.getElementById('join-button');
            button.disabled = true;
            button.innerHTML = '<svg class="spinner" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="3" stroke="currentColor" stroke-opacity="0.25"></circle><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path></svg> Connecting...';

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
                    alert(data.message || 'Cannot join meeting at this time. Please try again later.');
                    button.disabled = false;
                    button.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Join Meeting Now';
                    return;
                }

                button.style.display = 'none';
                document.getElementById('jitsi-container').style.display = 'block';

                const domain = data.domain;
                const options = {
                    roomName: data.room_name,
                    width: '100%',
                    height: '650px',
                    parentNode: document.querySelector('#jitsi-container'),
                    userInfo: {
                        displayName: data.display_name || 'Guest'
                    },
                    configOverwrite: {
                        prejoinPageEnabled: false,
                        startWithAudioMuted: true,
                        startWithVideoMuted: true,
                        enableWelcomePage: false,
                        enableClosePage: false
                    },
                    interfaceConfigOverwrite: {
                        SHOW_JITSI_WATERMARK: false,
                        DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
                        DISABLE_JOIN_LEAVE_NOTIFICATIONS: false
                    }
                };

                if (data.jwt) {
                    options.jwt = data.jwt;
                }

                jitsiApi = new JitsiMeetExternalAPI(domain, options);

                jitsiApi.addEventListener('readyToClose', () => {
                    window.location.reload();
                });

            } catch (error) {
                console.error('Error joining meeting:', error);
                alert('Failed to join meeting. Please refresh the page and try again.');
                button.disabled = false;
                button.innerHTML = '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Join Meeting Now';
            }
        }
    </script>
</body>
</html>
