<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Reminder</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
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
        .meeting-details {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Meeting Reminder</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <h2 style="margin: 0 0 10px 0; color: #92400e;">Your meeting starts in {{ $minutesUntilStart }} minutes!</h2>
                <p style="margin: 0; color: #78350f;">Don't forget to join: <strong>{{ $meeting->title }}</strong></p>
            </div>
            
            <div class="meeting-details">
                <p><strong>Time:</strong> {{ $meeting->start_at->format('g:i A') }} ({{ $meeting->timezone }})</p>
                <p><strong>Duration:</strong> {{ $meeting->start_at->diffInMinutes($meeting->end_at) }} minutes</p>
                @if($meeting->description)
                <p><strong>Details:</strong> {{ $meeting->description }}</p>
                @endif
            </div>
            
            <center>
                <a href="{{ $meetingUrl }}" class="button">Join Meeting Now</a>
            </center>
            
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                Make sure you have a stable internet connection and your camera/microphone ready.
            </p>
        </div>
        
        <div class="footer">
            <p>Automated reminder from Alora Admin</p>
        </div>
    </div>
</body>
</html>
