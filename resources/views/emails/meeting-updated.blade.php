<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Updated</title>
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
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
            background: #ede9fe;
            border-left: 4px solid #8b5cf6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .changes-list {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .change-item {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .change-item:last-child {
            border-bottom: none;
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
            <h1>🔄 Meeting Updated</h1>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <h2 style="margin: 0 0 10px 0; color: #5b21b6;">Meeting details have been updated</h2>
                <p style="margin: 0; color: #6b21a8;"><strong>{{ $meeting->title }}</strong></p>
            </div>
            
            @if(count($changes) > 0)
            <div class="changes-list">
                <h3 style="margin-top: 0;">What changed:</h3>
                @foreach($changes as $field => $change)
                <div class="change-item">
                    <strong>{{ ucfirst($field) }}:</strong><br>
                    <span style="color: #ef4444; text-decoration: line-through;">{{ $change['old'] }}</span>
                    →
                    <span style="color: #10b981;">{{ $change['new'] }}</span>
                </div>
                @endforeach
            </div>
            @endif
            
            <div class="meeting-details">
                <h3 style="margin-top: 0;">Current meeting details:</h3>
                <p><strong>Date:</strong> {{ $meeting->start_at->format('l, F j, Y') }}</p>
                <p><strong>Time:</strong> {{ $meeting->start_at->format('g:i A') }} - {{ $meeting->end_at->format('g:i A') }} ({{ $meeting->timezone }})</p>
                @if($meeting->description)
                <p><strong>Description:</strong> {{ $meeting->description }}</p>
                @endif
            </div>
            
            <center>
                <a href="{{ $meetingUrl }}" class="button">View Meeting Details</a>
            </center>
            
            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                Please update your calendar with the new details.
            </p>
        </div>
        
        <div class="footer">
            <p>Notification from AloraMeet</p>
        </div>
    </div>
</body>
</html>
