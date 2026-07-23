<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Models\Task;
use HomeSrv\Services\CalendarService;
use HomeSrv\Services\WeatherService;

Bootstrap::init();

header('Content-Type: application/json');

$tasks = Task::dashboard(50);

$today = date('Y-m-d');

$taskList = [];
foreach ($tasks as $task) {
    if ($task['next_due'] < $today) {
        $status = 'overdue';
    } elseif ($task['next_due'] === $today) {
        $status = 'today';
    } else {
        $status = 'future';
    }

    $taskList[] = [
        'id' => (int) $task['id'],
        'title' => $task['title'],
        'next_due' => $task['next_due'],
        'due_time' => $task['due_time'] ? substr($task['due_time'], 0, 5) : null,
        'recur_type' => $task['recur_type'],
        'assigned_name' => $task['assigned_name'],
        'status' => $status,
    ];
}

// Refresh calendar feeds if cache is stale (>15 min)
$feeds = CalendarFeed::enabled();
$needsRefresh = false;
foreach ($feeds as $feed) {
    if (!$feed['cached_at'] || (time() - strtotime($feed['cached_at'])) > 900) {
        $needsRefresh = true;
        break;
    }
}
if ($needsRefresh) {
    CalendarService::fetchAll();
}

$events = CalendarService::getUpcomingEvents(7);

// Filter to today and tomorrow only
$todayEvents = [];
$tomorrow = date('Y-m-d', strtotime('+1 day'));
foreach ($events as $event) {
    if ($event['date'] === $today || $event['date'] === $tomorrow) {
        $event['is_today'] = $event['date'] === $today;
        unset($event['date']); // client infers from is_today
        $todayEvents[] = $event;
    }
}

echo json_encode([
    'date' => date('l, F j, Y'),
    'date_iso' => $today,
    'tasks' => $taskList,
    'calendar_events' => $todayEvents,
    'weather' => WeatherService::get(),
], JSON_UNESCAPED_UNICODE);
