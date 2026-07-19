<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;
use HomeSrv\Services\TaskService;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Bootstrap::redirect('/tasks/');
    exit;
}

$taskId = (int) ($_POST['task_id'] ?? 0);
$task = TaskService::complete($taskId, Auth::userId());

if (Bootstrap::isHtmx()) {
    if ($task && !$task['is_completed']) {
        // Recurring — show updated card
        echo Bootstrap::blade()->run('components.task-card', ['task' => $task]);
    } else {
        // One-off completed — remove from list
        echo '';
    }
} else {
    Bootstrap::redirect('/tasks/');
}
