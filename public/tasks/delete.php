<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = (int) ($_POST['task_id'] ?? 0);
    Task::delete($taskId);
}

if (Bootstrap::isHtmx()) {
    echo '';
} else {
    Bootstrap::redirect('/tasks/');
}
