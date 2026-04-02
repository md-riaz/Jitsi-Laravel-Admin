<?php

namespace App\Services;

use App\Models\Meeting;
use Carbon\Carbon;

class CalendarService
{
    public function generateIcs(Meeting $meeting): string
    {
        $meetingUrl = route('meeting.show', ['meeting' => $meeting->id]);

        // Handle instant meetings
        if ($meeting->isInstantMeeting()) {
            $dtstart = now()->utc()->format('Ymd\THis\Z');
            $dtend = now()->addHour()->utc()->format('Ymd\THis\Z');
        } else {
            // Format dates for iCalendar (YYYYMMDDTHHMMSS format in UTC)
            $dtstart = $meeting->start_at->utc()->format('Ymd\THis\Z');
            $dtend = $meeting->end_at->utc()->format('Ymd\THis\Z');
        }

        $dtstamp = Carbon::now()->utc()->format('Ymd\THis\Z');

        // Generate unique identifier
        $uid = $meeting->id . '@jitsi-admin.local';

        // Escape special characters in text fields
        $summary = $this->escapeString($meeting->title);
        $description = $this->escapeString($this->buildDescription($meeting, $meetingUrl));
        $location = $this->escapeString($meetingUrl);

        // Build the iCalendar content
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Alora Admin//Meeting Scheduler//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$dtstamp}\r\n";
        $ics .= "DTSTART:{$dtstart}\r\n";
        $ics .= "DTEND:{$dtend}\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "SEQUENCE:0\r\n";

        // Add alarm only for scheduled meetings
        if (!$meeting->isInstantMeeting()) {
            $ics .= "BEGIN:VALARM\r\n";
            $ics .= "TRIGGER:-PT{$meeting->join_early_minutes}M\r\n";
            $ics .= "ACTION:DISPLAY\r\n";
            $ics .= "DESCRIPTION:Meeting starts in {$meeting->join_early_minutes} minutes\r\n";
            $ics .= "END:VALARM\r\n";
        }

        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }
    
    private function buildDescription(Meeting $meeting, string $meetingUrl): string
    {
        $description = '';

        if ($meeting->description) {
            $description .= $meeting->description . "\n\n";
        }

        $description .= "Join Meeting: {$meetingUrl}\n\n";
        $description .= "Meeting Details:\n";

        if ($meeting->isInstantMeeting()) {
            $description .= "- Type: Instant Meeting (No scheduled time)\n";
            $description .= "- Status: Available to join now\n";
        } else {
            $description .= "- Start: " . $meeting->start_at->format('F j, Y \a\t g:i A T') . "\n";
            $description .= "- End: " . $meeting->end_at->format('F j, Y \a\t g:i A T') . "\n";
            $description .= "- Duration: " . $meeting->start_at->diffInMinutes($meeting->end_at) . " minutes\n";
            $description .= "- You can join " . $meeting->join_early_minutes . " minutes early\n";
        }

        $description .= "\nPowered by Alora Admin";

        return $description;
    }
    
    private function escapeString(string $text): string
    {
        // Escape special characters for iCalendar format
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);
        
        return $text;
    }
}
