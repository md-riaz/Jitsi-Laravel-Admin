<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .meeting-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            width: 120px;
        }
        .detail-value {
            color: #1a1a1a;
        }
        .button {
            display: inline-block;
            background: #3b82f6;
            color: white !important;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background: #2563eb;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .description {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Meeting Invitation</h1>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>You've been invited to join the following meeting:</p>
            
            <h2 style="color: #1a1a1a; margin: 20px 0;">{{ $meeting->title }}</h2>
            
            @if($meeting->description)
            <div class="description">
                <strong>About this meeting:</strong><br>
                {{ $meeting->description }}
            </div>
            @endif
            
            <div class="meeting-details">
                <div class="detail-row">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">{{ $meeting->start_at->format('l, F j, Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Time</div>
                    <div class="detail-value">{{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }} ({{ $meeting->timezone }})</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Duration</div>
                    <div class="detail-value">{{ $meeting->start_at->diffInMinutes($meeting->end_at) }} minutes</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Organizer</div>
                    <div class="detail-value">{{ $meeting->creator->name }}</div>
                </div>
            </div>
            
            <center>
                <a href="{{ $inviteUrl }}" class="button">Accept Invitation & Join</a>
            </center>
            
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                <strong>Note:</strong> You can join the meeting {{ $meeting->join_early_minutes }} minutes before the scheduled start time.
            </p>
            
            <p style="color: #6b7280; font-size: 14px;">
                If you're unable to attend, please notify the organizer.
            </p>
        </div>
        
        <div class="footer">
            <p>This invitation was sent by Alora Admin</p>
            <p>A calendar invitation (.ics file) is attached to this email</p>
        </div>
    </div>
</body>
</html>
