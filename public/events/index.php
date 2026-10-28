<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Services\CalendarService;

Bootstrap::init();

$events = CalendarService::getUpcomingEvents(7, false);

$status = null;
foreach (['success', 'error'] as $type) {
    if ($message = Bootstrap::getFlash($type)) {
        $status = ['type' => $type, 'message' => $message];
    }
}

echo Bootstrap::blade()->run('events.index', ['events' => $events, 'status' => $status]);
