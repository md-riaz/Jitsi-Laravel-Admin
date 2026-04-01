<?php

return [
    'templates' => [
        [
            'template_key' => 'meeting_reminder',
            'name' => 'Meeting Reminder',
            'mailable_class' => 'App\\Mail\\MeetingReminderMail',
            'view_name' => 'emails.meeting-reminder',
            'subject_template' => 'Reminder: {{meeting_title}} starts in {{minutes_until_start}} minutes',
            'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a1a1a; background: #f9fafb; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background: #d97706; color: #ffffff; padding: 24px; text-align: center; font-size: 24px; font-weight: 700;">Meeting Reminder</div>
        <div style="padding: 24px;">
            <p><strong>{{meeting_title}}</strong> starts in <strong>{{minutes_until_start}} minutes</strong>.</p>
            <p><strong>Date:</strong> {{meeting_date}}</p>
            <p><strong>Time:</strong> {{meeting_time}}</p>
            <p><strong>Duration:</strong> {{meeting_duration_minutes}} minutes</p>
            <p style="margin-top: 24px;"><a href="{{meeting_url}}" style="background: #2563eb; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px;">Join Meeting</a></p>
        </div>
    </div>
</body>
</html>
HTML,
        ],
        [
            'template_key' => 'meeting_cancelled',
            'name' => 'Meeting Cancelled',
            'mailable_class' => 'App\\Mail\\MeetingCancelledMail',
            'view_name' => 'emails.meeting-cancelled',
            'subject_template' => 'Cancelled: {{meeting_title}}',
            'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Cancelled</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a1a1a; background: #f9fafb; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background: #dc2626; color: #ffffff; padding: 24px; text-align: center; font-size: 24px; font-weight: 700;">Meeting Cancelled</div>
        <div style="padding: 24px;">
            <p>The following meeting has been cancelled:</p>
            <h2 style="margin: 12px 0;">{{meeting_title}}</h2>
            <p><strong>Previously scheduled:</strong> {{meeting_datetime}}</p>
            <p><strong>Organizer:</strong> {{organizer_name}}</p>
            <p><strong>Reason:</strong> {{cancellation_reason}}</p>
        </div>
    </div>
</body>
</html>
HTML,
        ],
        [
            'template_key' => 'meeting_updated',
            'name' => 'Meeting Updated',
            'mailable_class' => 'App\\Mail\\MeetingUpdatedMail',
            'view_name' => 'emails.meeting-updated',
            'subject_template' => 'Updated: {{meeting_title}}',
            'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Updated</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a1a1a; background: #f9fafb; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <div style="background: #7c3aed; color: #ffffff; padding: 24px; text-align: center; font-size: 24px; font-weight: 700;">Meeting Updated</div>
        <div style="padding: 24px;">
            <p>The meeting <strong>{{meeting_title}}</strong> has been updated.</p>
            <div>{{changes_html}}</div>
            <p><strong>Current Date:</strong> {{meeting_date}}</p>
            <p><strong>Current Time:</strong> {{meeting_time}}</p>
            <p style="margin-top: 24px;"><a href="{{meeting_url}}" style="background: #2563eb; color: #ffffff; padding: 12px 20px; text-decoration: none; border-radius: 6px;">View Meeting</a></p>
        </div>
    </div>
</body>
</html>
HTML,
        ],
    ],
];
