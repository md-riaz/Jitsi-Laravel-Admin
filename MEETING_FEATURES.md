# Meeting Management & Security Features

This document describes all meeting management and security features implemented in the Jitsi-Laravel-Admin platform.

## Table of Contents

1. [Meeting Types](#meeting-types)
2. [Security Controls](#security-controls)
3. [Recurring Meetings](#recurring-meetings)
4. [Invitation System](#invitation-system)
5. [Meeting Lifecycle](#meeting-lifecycle)
6. [Configuration](#configuration)
7. [API Reference](#api-reference)

---

## Meeting Types

### Instant Meetings
- Start immediately upon creation
- No start/end times required
- Status automatically set to 'live'
- Can be joined immediately
- No join window restrictions

**Example:**
```php
Meeting::create([
    'title' => 'Quick Team Sync',
    'meeting_type' => 'instant',
    'status' => 'live',
]);
```

### Scheduled Meetings
- Require start_at and end_at times
- Support timezone selection (15 major timezones)
- Status transitions: scheduled → live → ended
- Join window enforcement (early/late minutes)

**Example:**
```php
Meeting::create([
    'title' => 'Weekly Team Meeting',
    'meeting_type' => 'scheduled',
    'start_at' => '2026-02-25 10:00:00',
    'end_at' => '2026-02-25 11:00:00',
    'timezone' => 'America/New_York',
    'join_early_minutes' => 10,
    'join_late_minutes' => 60,
]);
```

---

## Security Controls

### 1. Password Protection
**Field:** `password` (string, nullable)

- Automatically hashed with bcrypt on create/update
- Minimum 4 characters
- Validated at join time via `verifyPassword()` method

**Usage:**
```php
$meeting = Meeting::create([
    'title' => 'Confidential Meeting',
    'password' => 'secret123', // Will be auto-hashed
]);

// Verify password
if ($meeting->verifyPassword($userInput)) {
    // Allow join
}
```

### 2. Lobby/Waiting Room
**Field:** `lobby_enabled` (boolean, default: true)

- When enabled, participants wait in lobby until admitted by moderator
- Passed to Jitsi as `prejoinPageEnabled` config
- Moderators can admit participants individually

**Usage:**
```php
Meeting::create([
    'lobby_enabled' => true, // Enable waiting room
]);
```

### 3. Guest Access Control
**Field:** `allow_guests` (boolean, default: true)

- Controls whether unauthenticated users can join
- Enforced in `MeetingJoinController`
- Overrides visibility settings

**Usage:**
```php
Meeting::create([
    'allow_guests' => false, // Require authentication
]);
```

### 4. Participant Limit
**Field:** `max_participants` (integer, nullable)

- Limits total number of concurrent participants
- Range: 2-1000
- Real-time enforcement using joined_at/left_at tracking
- Returns 403 with current count when full

**Usage:**
```php
Meeting::create([
    'max_participants' => 50, // Maximum 50 participants
]);
```

### 5. IP Restriction
**Fields:** 
- `allowed_ips` (text, nullable) - One IP/CIDR per line
- `ip_restriction_enabled` (boolean, default: false)

- Supports individual IPs and CIDR notation
- CIDR examples: `192.168.1.0/24`, `10.0.0.0/8`
- Validated via `isIpAllowed($ip)` method

**Usage:**
```php
Meeting::create([
    'ip_restriction_enabled' => true,
    'allowed_ips' => "192.168.1.0/24\n10.0.0.50",
]);

// Check IP
if ($meeting->isIpAllowed($request->ip())) {
    // Allow join
}
```

### 6. JWT Authentication (Org Policy)
**Organization Fields:**
- `require_jwt` (boolean, default: false)
- `jwt_expiry_minutes` (integer, default: 120)

- Organizations can mandate JWT for all meetings
- Personal meetings have optional JWT
- JWT includes user info and moderator flag
- Respects org expiry settings

**Usage:**
```php
// Enable JWT requirement for organization
$organization->update([
    'require_jwt' => true,
    'jwt_expiry_minutes' => 180, // 3 hours
]);

// JWT automatically enforced for org meetings
```

---

## Recurring Meetings

### RecurrenceRule Model

**Fields:**
- `frequency`: daily, weekly, monthly, yearly
- `interval`: Every N days/weeks/months/years
- `count`: Number of occurrences (optional)
- `until_date`: End date (optional)
- `by_day`: Weekdays for weekly (e.g., "MO,WE,FR")
- `by_month_day`: Days for monthly (e.g., "1,15,30")
- `exceptions`: JSON array of excluded dates

### Examples

**Daily Recurring:**
```php
RecurrenceRule::create([
    'meeting_id' => $meeting->id,
    'frequency' => 'daily',
    'interval' => 1,
    'count' => 30, // 30 occurrences
]);
```

**Weekly Recurring:**
```php
RecurrenceRule::create([
    'meeting_id' => $meeting->id,
    'frequency' => 'weekly',
    'interval' => 1,
    'by_day' => 'MO,WE,FR', // Monday, Wednesday, Friday
    'until_date' => '2026-12-31',
]);
```

**Monthly Recurring:**
```php
RecurrenceRule::create([
    'meeting_id' => $meeting->id,
    'frequency' => 'monthly',
    'interval' => 1,
    'by_month_day' => '15', // 15th of each month
    'count' => 12,
]);
```

### RecurrenceService

**Generate Occurrences:**
```php
$service = app(RecurrenceService::class);
$occurrences = $service->generateOccurrences($rule, limit: 50);
// Returns array of CarbonImmutable dates
```

**Human-Readable Description:**
```php
$description = $service->toHumanReadable($rule);
// Example: "Every week, on Monday, Wednesday, Friday, for 10 occurrences"
```

---

## Invitation System

### Email Invitations

**MeetingInviteService:**
```php
$service = app(MeetingInviteService::class);

// Create and send invite
$result = $service->createInvite($meeting, 'user@example.com', sendEmail: true);

// Returns:
[
    'invite' => MeetingInvite,
    'token' => 'plain-text-token',
    'email_sent' => true/false,
    'email_error' => 'error message' // if failed
]
```

**Features:**
- Secure 64-character random tokens
- Bcrypt hashed storage
- Auto-expiry: meeting end + 1 day (or 1 week for instant)
- Email includes .ics calendar attachment
- Graceful failure handling

**Invite URL:**
```
https://your-domain.com/invite/{token}
```

### Manual Invites (Link Sharing)

**Visibility Options:**
- `invite_only` - Only invited users/guests
- `link_anyone` - Anyone with link can join
- `org_only` - Organization members only

---

## Meeting Lifecycle

### Status Transitions

**States:**
- `scheduled` - Future meeting
- `live` - Currently active
- `ended` - Completed
- `canceled` - Canceled by organizer

**Automated Transitions:**

Run the scheduler:
```bash
# Add to crontab
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

The `meetings:update-statuses` command runs every minute:
```bash
php artisan meetings:update-statuses
```

**Manual Status Updates:**
```php
// Cancel meeting
$meeting->update(['status' => 'canceled']);

// Force end meeting
$meeting->update(['status' => 'ended']);
```

### Join Window

**Configuration:**
- `join_early_minutes`: How long before start users can join (default: 10)
- `join_late_minutes`: How long after end users can join (default: 60)

**Validation:**
```php
$canJoin = $meeting->canJoinAt(Carbon::now());
```

---

## Configuration

### Environment Variables

```env
# Jitsi JWT Configuration
JITSI_DOMAIN=meet.your-domain.com
JITSI_JWT_SECRET=your-secret-key
JITSI_JWT_ISSUER=your-app-id
JITSI_JWT_AUDIENCE=jitsi
JITSI_JWT_SUB=meet.your-domain.com

# Mail Configuration (for invitations)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Default Security Settings

**Meeting Defaults:**
```php
protected $attributes = [
    'lobby_enabled' => true,        // Waiting room enabled
    'allow_guests' => true,         // Guests allowed
    'ip_restriction_enabled' => false, // No IP restriction
    'join_early_minutes' => 10,     // 10 minutes early
    'join_late_minutes' => 60,      // 60 minutes late
];
```

**Organization Defaults:**
```php
protected $attributes = [
    'require_jwt' => false,         // JWT optional
    'jwt_expiry_minutes' => 120,    // 2 hours
];
```

---

## API Reference

### MeetingJoinController

**Endpoint:** `POST /api/meetings/{meeting}/join`

**Security Checks (in order):**
1. IP restriction
2. Participant limit
3. Password verification
4. Guest policy
5. Join window
6. JWT requirement

**Request Parameters:**
- `password` (optional) - Meeting password
- `display_name` (optional) - For guest users

**Response (Success):**
```json
{
    "can_join": true,
    "room_name": "mtg_abc123def456",
    "domain": "meet.your-domain.com",
    "jwt": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "display_name": "John Doe",
    "is_moderator": false,
    "config": {
        "roomName": "mtg_abc123def456",
        "width": "100%",
        "height": 600,
        "userInfo": {
            "displayName": "John Doe",
            "email": "john@example.com"
        },
        "configOverwrite": {
            "prejoinPageEnabled": true
        }
    }
}
```

**Error Responses:**
- `403` - IP not allowed
- `403` - Meeting full
- `403` - Invalid password
- `403` - Guests not allowed
- `403` - Outside join window
- `500` - JWT required but not configured

### Meeting Model Methods

```php
// Security
$meeting->verifyPassword($password);      // Returns bool
$meeting->isIpAllowed($ip);                // Returns bool
$meeting->getAllowedIps();                 // Returns array

// Lifecycle
$meeting->canJoinAt($now);                 // Returns bool
$meeting->isInstantMeeting();              // Returns bool

// Relationships
$meeting->organization;
$meeting->creator;
$meeting->participants;
$meeting->invites;
$meeting->events;
$meeting->recurrenceRule;
```

---

## Troubleshooting

### Common Issues

**1. Meetings stuck in 'scheduled' status**
- Check if Laravel scheduler is running
- Run manually: `php artisan meetings:update-statuses`
- Verify cron job: `crontab -l`

**2. Email invitations not sending**
- Check mail configuration in `.env`
- Test mail: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));`
- Check logs: `storage/logs/laravel.log`

**3. JWT not working**
- Verify `JITSI_JWT_SECRET` is set
- Check Jitsi server JWT configuration matches
- Ensure `require_jwt` is true for organization

**4. IP restriction not working**
- Verify `ip_restriction_enabled` is true
- Check IP format (CIDR or exact match)
- Test: `$meeting->isIpAllowed('192.168.1.100')`

**5. Participant limit not enforced**
- Check `max_participants` is set
- Verify query counts joined participants correctly
- Check participant records have proper `joined_at`/`left_at`

---

## Security Best Practices

1. **Always use HTTPS** for production
2. **Enable JWT** for organization meetings
3. **Use strong passwords** (minimum 8 characters recommended)
4. **Enable lobby** for sensitive meetings
5. **Restrict IP ranges** for internal meetings
6. **Set participant limits** to prevent resource exhaustion
7. **Disable guests** for confidential meetings
8. **Monitor meeting events** in `meeting_events` table
9. **Regular security audits** of meeting configurations
10. **Keep Laravel and dependencies updated**

---

## Performance Considerations

### Database Indexes

Existing indexes:
- `meetings.organization_id, meetings.start_at`
- `meetings.status`
- `recurrence_rules.meeting_id`
- `meeting_participants.meeting_id`
- `meeting_invites.meeting_id`
- `meeting_events.meeting_id, meeting_events.type`

### Caching Recommendations

```php
// Cache active meetings
Cache::remember('active_meetings', 60, function () {
    return Meeting::where('status', 'live')->get();
});

// Cache organization JWT policy
Cache::remember("org_{$orgId}_jwt_policy", 3600, function () use ($orgId) {
    return Organization::find($orgId)->require_jwt;
});
```

### Queue Jobs

For production, consider queuing:
- Email invitations: `SendMeetingInvite` job
- Status updates: Queue recurring job instead of cron
- Large recurrence generation: Background processing

---

## License

This feature set is part of the Jitsi-Laravel-Admin platform.
