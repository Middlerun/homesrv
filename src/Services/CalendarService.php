<?php

namespace HomeSrv\Services;

use DateTimeImmutable;
use DateTimeZone;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Models\HiddenEvent;
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
     *
     * @param int $days Number of days ahead to include.
     * @param bool $excludeHidden When true, hidden events are omitted (dashboard view).
     * @return array
     */
    public static function getUpcomingEvents(int $days = 7, bool $excludeHidden = true): array
    {
        $tz = new DateTimeZone(Setting::get('timezone', 'UTC'));
        $now = new DateTimeImmutable('today', $tz);
        $end = $now->modify("+{$days} days")->setTime(23, 59, 59);

        $feeds = CalendarFeed::enabled();
        $hiddenMap = HiddenEvent::hiddenUids();
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
                    $local = $isAllDay ? $dtstart : $dtstart->setTimezone($tz);

                    $dtend = isset($vevent->DTEND)
                        ? $vevent->DTEND->getDateTime()
                        : $dtstart;
                    $endLocal = $isAllDay ? $dtend : $dtend->setTimezone($tz);
                    $isOver = $endLocal < new DateTimeImmutable('now', $tz);

                    $uid = (string) $vevent->UID;
                    if ($uid === '') {
                        $uid = 'no-uid-' . md5((string) $vevent->SUMMARY . $dtstart->format('Y-m-d H:i:s'));
                    }

                    $isHidden = isset($hiddenMap[(int) $feed['id']][$uid]);
                    if ($excludeHidden && $isHidden) {
                        continue;
                    }

                    $events[] = [
                        'uid' => $uid,
                        'feed_id' => (int) $feed['id'],
                        'summary' => (string) $vevent->SUMMARY,
                        'date' => $local->format('Y-m-d'),
                        'time' => $isAllDay ? null : $local->format('g:ia'),
                        'color' => $feed['color'],
                        'feed_name' => $feed['name'],
                        'all_day' => $isAllDay,
                        'is_over' => $isOver,
                        'hidden' => $isHidden,
                        '_sort_time' => $local->format('H:i'),
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
            return strcmp($a['_sort_time'] ?? '', $b['_sort_time'] ?? '');
        });

        // Drop the sort helper before returning
        foreach ($events as &$event) {
            unset($event['_sort_time']);
        }
        unset($event);

        return $events;
    }
}
