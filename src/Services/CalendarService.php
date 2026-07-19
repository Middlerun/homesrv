<?php

namespace HomeSrv\Services;

use DateTimeImmutable;
use DateTimeZone;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Models\Setting;
use Sabre\VObject\Reader;

class CalendarService
{
    /**
     * Fetch and cache all enabled feeds.
     */
    public static function fetchAll(): int
    {
        $feeds = CalendarFeed::enabled();
        $count = 0;

        foreach ($feeds as $feed) {
            if (self::fetchFeed($feed['id'], $feed['url'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch a single feed and cache it.
     */
    public static function fetchFeed(int $id, string $url): bool
    {
        $ctx = stream_context_create([
            'http' => ['timeout' => 15],
            'ssl' => ['verify_peer' => true],
        ]);

        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) {
            return false;
        }

        CalendarFeed::updateCache($id, $data);
        return true;
    }

    /**
     * Get upcoming events from all enabled feeds for the next N days.
     */
    public static function getUpcomingEvents(int $days = 7): array
    {
        $tz = new DateTimeZone(Setting::get('timezone', 'UTC'));
        $now = new DateTimeImmutable('today', $tz);
        $end = $now->modify("+{$days} days")->setTime(23, 59, 59);

        $feeds = CalendarFeed::enabled();
        $events = [];

        foreach ($feeds as $feed) {
            if (empty($feed['cached_data'])) {
                continue;
            }

            try {
                $vcalendar = Reader::read($feed['cached_data']);
                $expanded = $vcalendar->expand($now, $end, $tz);

                foreach ($expanded->VEVENT ?? [] as $vevent) {
                    $dtstart = $vevent->DTSTART->getDateTime();
                    $isAllDay = !$vevent->DTSTART->hasTime();

                    $events[] = [
                        'summary' => (string) $vevent->SUMMARY,
                        'date' => $dtstart->format('Y-m-d'),
                        'time' => $isAllDay ? null : $dtstart->setTimezone($tz)->format('g:ia'),
                        'color' => $feed['color'],
                        'feed_name' => $feed['name'],
                        'all_day' => $isAllDay,
                    ];
                }
            } catch (\Exception $e) {
                // Skip malformed feeds
                continue;
            }
        }

        // Sort by date, then time (all-day first)
        usort($events, function ($a, $b) {
            $cmp = strcmp($a['date'], $b['date']);
            if ($cmp !== 0) return $cmp;
            if ($a['all_day'] && !$b['all_day']) return -1;
            if (!$a['all_day'] && $b['all_day']) return 1;
            return strcmp($a['time'] ?? '', $b['time'] ?? '');
        });

        return $events;
    }
}
