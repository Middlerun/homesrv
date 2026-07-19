<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Services\CalendarService;

Bootstrap::init();

$events = CalendarService::getUpcomingEvents(7);

echo Bootstrap::blade()->run('dashboard.partials.calendar', ['events' => $events]);
