<?php

namespace HomeSrv\Services;

use DateTimeImmutable;

class RecurrenceCalculator
{
    /**
     * Calculate the next due date after completing a task.
     *
     * @param array $task Task row with recur_type, recur_interval, recur_days, next_due
     * @param DateTimeImmutable|null $completedOn Date of completion (defaults to today)
     * @return string Next due date as Y-m-d
     */
    public static function advance(array $task, ?DateTimeImmutable $completedOn = null): string
    {
        $completedOn ??= new DateTimeImmutable('today');
        $currentDue = new DateTimeImmutable($task['next_due']);
        $interval = max(1, (int) ($task['recur_interval'] ?? 1));

        return match ($task['recur_type']) {
            'daily' => self::advanceDaily($currentDue, $interval),
            'weekly' => self::advanceWeekly($currentDue, $interval, $task['recur_days'] ?? null),
            'monthly' => self::advanceMonthly($currentDue, $interval),
            'interval' => self::advanceInterval($completedOn, $interval),
            default => $currentDue->format('Y-m-d'),
        };
    }

    private static function advanceDaily(DateTimeImmutable $due, int $interval): string
    {
        return $due->modify("+{$interval} days")->format('Y-m-d');
    }

    private static function advanceWeekly(DateTimeImmutable $due, int $interval, ?string $recurDays): string
    {
        if ($recurDays === null || $recurDays === '') {
            return $due->modify('+' . (7 * $interval) . ' days')->format('Y-m-d');
        }

        // Parse days (1=Mon..7=Sun) and sort
        $days = array_map('intval', explode(',', $recurDays));
        sort($days);

        // Current ISO day of week (1=Mon..7=Sun)
        $currentDay = (int) $due->format('N');

        // Find the next day in the set after today
        foreach ($days as $day) {
            if ($day > $currentDay) {
                $diff = $day - $currentDay;
                return $due->modify("+{$diff} days")->format('Y-m-d');
            }
        }

        // Wrap to first day of next cycle
        $firstDay = $days[0];
        $daysUntilEndOfWeek = 7 - $currentDay;
        $skipWeeks = ($interval - 1) * 7;
        $daysToAdd = $daysUntilEndOfWeek + $skipWeeks + $firstDay;

        return $due->modify("+{$daysToAdd} days")->format('Y-m-d');
    }

    private static function advanceMonthly(DateTimeImmutable $due, int $interval): string
    {
        return $due->modify("+{$interval} months")->format('Y-m-d');
    }

    private static function advanceInterval(DateTimeImmutable $completedOn, int $interval): string
    {
        return $completedOn->modify("+{$interval} days")->format('Y-m-d');
    }
}
