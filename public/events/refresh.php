<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Services\CalendarService;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Bootstrap::redirect('/events/');
}

$total = count(CalendarFeed::enabled());
$fetched = CalendarService::fetchAll(true);

if ($total === 0) {
    $status = ['type' => 'error', 'message' => 'No calendar feeds are enabled.'];
} elseif ($fetched === $total) {
    $status = ['type' => 'success', 'message' => "Refreshed $fetched calendar feed(s)."];
} else {
    $status = ['type' => 'error', 'message' => "Refreshed $fetched of $total calendar feeds; the rest could not be fetched."];
}

if (Bootstrap::isHtmx()) {
    $events = CalendarService::getUpcomingEvents(7, false);
    echo Bootstrap::blade()->run('events._list', ['events' => $events, 'status' => $status]);
} else {
    Bootstrap::flash($status['type'], $status['message']);
    Bootstrap::redirect('/events/');
}
