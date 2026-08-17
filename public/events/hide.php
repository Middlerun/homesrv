<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\HiddenEvent;
use HomeSrv\Services\CalendarService;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Bootstrap::redirect('/events/');
}

$feedId = (int) ($_POST['feed_id'] ?? 0);
$uid = (string) ($_POST['uid'] ?? '');

if ($feedId > 0 && $uid !== '') {
    HiddenEvent::hide($feedId, $uid);
}

$events = CalendarService::getUpcomingEvents(7, false);

if (Bootstrap::isHtmx()) {
    echo Bootstrap::blade()->run('events._list', ['events' => $events]);
} else {
    Bootstrap::redirect('/events/');
}
