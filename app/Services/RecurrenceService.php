<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\RecurrenceRule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class RecurrenceService
{
    /**
     * Generate occurrence dates for a recurring meeting
     *
     * @param RecurrenceRule $rule
     * @param int $limit Maximum number of occurrences to generate
     * @return array Array of Carbon dates
     */
    public function generateOccurrences(RecurrenceRule $rule, int $limit = 100): array
    {
        $meeting = $rule->meeting;
        if (!$meeting->start_at) {
            return [];
        }

        $occurrences = [];
        $current = CarbonImmutable::parse($meeting->start_at);
        $count = 0;

        $exceptions = $rule->exceptions ?? [];

        while ($count < $limit) {
            // Check if we've reached the count limit
            if ($rule->count && count($occurrences) >= $rule->count) {
                break;
            }

            // Check if we've reached the until date
            if ($rule->until_date && $current->isAfter($rule->until_date)) {
                break;
            }

            // Check if this date is an exception
            $dateString = $current->format('Y-m-d');
            if (!in_array($dateString, $exceptions)) {
                $occurrences[] = $current;
            }

            // Calculate next occurrence based on frequency
            $current = $this->getNextOccurrence($current, $rule);
            $count++;

            // Safety break to prevent infinite loops
            if ($count > 1000) {
                break;
            }
        }

        return $occurrences;
    }

    /**
     * Get the next occurrence date based on the recurrence rule
     */
    private function getNextOccurrence(CarbonImmutable $current, RecurrenceRule $rule): CarbonImmutable
    {
        $interval = $rule->interval ?? 1;

        switch ($rule->frequency) {
            case 'daily':
                return $current->addDays($interval);

            case 'weekly':
                if ($rule->by_day) {
                    return $this->getNextWeekdayOccurrence($current, $rule->by_day, $interval);
                }
                return $current->addWeeks($interval);

            case 'monthly':
                if ($rule->by_month_day) {
                    return $this->getNextMonthDayOccurrence($current, $rule->by_month_day, $interval);
                }
                return $current->addMonths($interval);

            case 'yearly':
                return $current->addYears($interval);

            default:
                return $current->addWeeks($interval);
        }
    }

    /**
     * Get next occurrence for weekly recurrence with specific days
     */
    private function getNextWeekdayOccurrence(CarbonImmutable $current, string $byDay, int $interval): CarbonImmutable
    {
        $weekdays = explode(',', $byDay);
        $dayMap = [
            'SU' => Carbon::SUNDAY,
            'MO' => Carbon::MONDAY,
            'TU' => Carbon::TUESDAY,
            'WE' => Carbon::WEDNESDAY,
            'TH' => Carbon::THURSDAY,
            'FR' => Carbon::FRIDAY,
            'SA' => Carbon::SATURDAY,
        ];

        $targetDays = array_map(fn($day) => $dayMap[trim($day)] ?? null, $weekdays);
        $targetDays = array_filter($targetDays);

        if (empty($targetDays)) {
            return $current->addWeeks($interval);
        }

        // Find next matching day
        $next = $current->addDay();
        $weeksAdded = 0;

        while ($weeksAdded < 8) { // Safety limit
            if (in_array($next->dayOfWeek, $targetDays)) {
                return $next;
            }

            $next = $next->addDay();

            // If we've cycled through all target days in the week, jump to next interval
            if ($next->dayOfWeek === min($targetDays) && $weeksAdded > 0) {
                $next = $next->addWeeks($interval - 1);
            }

            $weeksAdded = $current->diffInWeeks($next);
        }

        return $current->addWeeks($interval);
    }

    /**
     * Get next occurrence for monthly recurrence with specific days
     */
    private function getNextMonthDayOccurrence(CarbonImmutable $current, string $byMonthDay, int $interval): CarbonImmutable
    {
        $days = array_map('intval', explode(',', $byMonthDay));
        sort($days);

        $currentDay = $current->day;
        $nextDay = null;

        // Find next day in current month
        foreach ($days as $day) {
            if ($day > $currentDay) {
                $nextDay = $day;
                break;
            }
        }

        if ($nextDay) {
            // Try to set to next day in current month
            try {
                return $current->setDay($nextDay);
            } catch (\Exception $e) {
                // Day doesn't exist in this month, skip to next month
            }
        }

        // Move to next interval month and use first day in the list
        $next = $current->addMonths($interval);
        try {
            return $next->setDay($days[0]);
        } catch (\Exception $e) {
            return $next;
        }
    }

    /**
     * Convert recurrence rule to human-readable string
     */
    public function toHumanReadable(RecurrenceRule $rule): string
    {
        $parts = [];

        switch ($rule->frequency) {
            case 'daily':
                $parts[] = $rule->interval > 1 ? "Every {$rule->interval} days" : "Every day";
                break;
            case 'weekly':
                $parts[] = $rule->interval > 1 ? "Every {$rule->interval} weeks" : "Every week";
                if ($rule->by_day) {
                    $parts[] = "on " . $this->formatWeekdays($rule->by_day);
                }
                break;
            case 'monthly':
                $parts[] = $rule->interval > 1 ? "Every {$rule->interval} months" : "Every month";
                if ($rule->by_month_day) {
                    $parts[] = "on day(s) " . $rule->by_month_day;
                }
                break;
            case 'yearly':
                $parts[] = $rule->interval > 1 ? "Every {$rule->interval} years" : "Every year";
                break;
        }

        if ($rule->count) {
            $parts[] = "for {$rule->count} occurrences";
        } elseif ($rule->until_date) {
            $parts[] = "until " . $rule->until_date->format('Y-m-d');
        }

        return implode(', ', $parts);
    }

    private function formatWeekdays(string $byDay): string
    {
        $dayNames = [
            'SU' => 'Sunday',
            'MO' => 'Monday',
            'TU' => 'Tuesday',
            'WE' => 'Wednesday',
            'TH' => 'Thursday',
            'FR' => 'Friday',
            'SA' => 'Saturday',
        ];

        $days = explode(',', $byDay);
        $formatted = array_map(fn($day) => $dayNames[trim($day)] ?? $day, $days);

        return implode(', ', $formatted);
    }
}
