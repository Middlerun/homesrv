<?php

namespace HomeSrv\Services;

use DateTimeImmutable;
use DateTimeZone;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Models\Setting;
use HomeSrv\Models\Task;

/**
 * The data behind the Kobo dashboard: today's tasks, today's and tomorrow's
 * events, and the weather.
 *
 * Both /api/dashboard.php (raw data) and /api/ui.php (a laid-out UI tree built
 * from it) read from here, and the fixtures in tests/ui/fixtures/ are recordings
 * of this shape.
 */
class DashboardService
{
    public static function timezone(): DateTimeZone
    {
        return new DateTimeZone(Setting::get('timezone', 'UTC'));
    }

    public static function data(): array
    {
        $now = new DateTimeImmutable('now', self::timezone());
        $today = $now->format('Y-m-d');
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');

        return [
            'date' => $now->format('l, F j, Y'),
            'date_iso' => $today,
            'tasks' => self::tasks($today),
            'calendar_events' => self::events($today, $tomorrow),
            'weather' => WeatherService::get(),
        ];
    }

    private static function tasks(string $today): array
    {
        $list = [];
        foreach (Task::dashboard(50) as $task) {
            if ($task['next_due'] < $today) {
                $status = 'overdue';
            } elseif ($task['next_due'] === $today) {
                $status = 'today';
            } else {
                $status = 'future';
            }

            $list[] = [
                'id' => (int) $task['id'],
                'title' => $task['title'],
                'next_due' => $task['next_due'],
                'due_time' => $task['due_time'] ? substr($task['due_time'], 0, 5) : null,
                'recur_type' => $task['recur_type'],
                'assigned_name' => $task['assigned_name'],
                'status' => $status,
            ];
        }
        return $list;
    }

    /** Today's and tomorrow's events; the client infers the day from is_today. */
    private static function events(string $today, string $tomorrow): array
    {
        // Refresh calendar feeds if the cache is stale (>15 min).
        foreach (CalendarFeed::enabled() as $feed) {
            if (!$feed['cached_at'] || (time() - strtotime($feed['cached_at'])) > 900) {
                CalendarService::fetchAll();
                break;
            }
        }

        $out = [];
        foreach (CalendarService::getUpcomingEvents(7) as $event) {
            if ($event['date'] !== $today && $event['date'] !== $tomorrow) {
                continue;
            }
            $event['is_today'] = $event['date'] === $today;
            unset($event['date']);
            $out[] = $event;
        }
        return $out;
    }
}
