<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Meeting Invitation - {{ $meeting->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: #2563eb;
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 1.75rem;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.95;
            font-size: 1rem;
        }
        .content {
            padding: 40px 30px;
        }
        .meeting-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .meeting-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            width: 120px;
            flex-shrink: 0;
        }
        .info-value {
            color: #1a1a1a;
        }
        .description-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 8px;
        }
        .note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin-top: 24px;
            border-radius: 4px;
            font-size: 0.875rem;
            color: #78350f;
        }
        @media (max-width: 640px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 You're Invited!</h1>
            <p>Join the meeting with your details below</p>
        </div>

        <div class="content">
            <div class="meeting-info">
                <div class="meeting-title">{{ $meeting->title }}</div>
                
                @if($meeting->description)
                <div class="description-box">
                    <strong>About:</strong><br>
                    {{ $meeting->description }}
                </div>
                @endif
                
                <div class="info-row">
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ $meeting->start_at->format('l, F j, Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Time</div>
                    <div class="info-value">{{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }} ({{ $meeting->timezone }})</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Duration</div>
                    <div class="info-value">{{ $meeting->start_at->diffInMinutes($meeting->end_at) }} minutes</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Organizer</div>
                    <div class="info-value">{{ $meeting->creator->name }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('invite.accept', ['token' => $token]) }}">
                @csrf
                
                <div class="form-group">
                    <label for="display_name">Your Name *</label>
                    <input 
                        type="text" 
                        id="display_name" 
                        name="display_name" 
                        value="{{ old('display_name') }}"
                        placeholder="Enter your full name"
                        required
                        autofocus
                    >
                    @error('display_name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        value="{{ $invite->email }}"
                        disabled
                        style="background: #f9fafb;"
                    >
                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 6px;">
                        This invitation was sent to this email address
                    </p>
                </div>

                <button type="submit" class="btn">
                    Accept Invitation & Continue
                </button>
            </form>

            <div class="note">
                <strong>Note:</strong> You can join {{ $meeting->join_early_minutes }} minutes before the scheduled start time.
            </div>
        </div>
    </div>
</body>
</html>
