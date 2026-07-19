<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;
use HomeSrv\Services\TaskService;

Bootstrap::init();

$taskId = (int) ($_GET['task_id'] ?? $_POST['task_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return defer modal
    $task = Task::find($taskId);
    if ($task) {
        echo Bootstrap::blade()->run('components.defer-modal', ['task' => $task]);
    }
    exit;
}

// POST — perform defer
$newDue = $_POST['new_due'] ?? '';
$task = TaskService::defer($taskId, Auth::userId(), $newDue);

if (Bootstrap::isHtmx()) {
    if ($task) {
        echo Bootstrap::blade()->run('components.task-card', ['task' => $task]);
    }
} else {
    Bootstrap::redirect('/tasks/');
}
