<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Cancelled</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
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
            <h1>❌ Meeting Cancelled</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <h2 style="margin: 0 0 10px 0; color: #991b1b;">This meeting has been cancelled</h2>
                <p style="margin: 0; color: #7f1d1d;"><strong>{{ $meeting->title }}</strong></p>
            </div>
            
            <div class="meeting-details">
                <p><strong>Was scheduled for:</strong> {{ $meeting->start_at->format('l, F j, Y \a\t g:i A') }}</p>
                <p><strong>Organizer:</strong> {{ $meeting->creator->name }}</p>
                @if($reason)
                <p><strong>Cancellation reason:</strong></p>
                <p style="background: white; padding: 15px; border-radius: 4px; margin-top: 10px;">{{ $reason }}</p>
                @endif
            </div>
            
            <p>Please remove this meeting from your calendar. We apologize for any inconvenience.</p>
            
            <p style="color: #6b7280; font-size: 14px;">
                If you have questions, please contact the meeting organizer.
            </p>
        </div>
        
        <div class="footer">
            <p>Notification from Alora Admin</p>
        </div>
    </div>
</body>
</html>
