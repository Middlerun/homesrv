<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;

Bootstrap::init();

$filter = $_GET['filter'] ?? 'active';

$tasks = Task::list($filter);

if (Bootstrap::isHtmx()) {
    echo Bootstrap::blade()->run('tasks._task-filtered', ['tasks' => $tasks, 'filter' => $filter]);
} else {
    echo Bootstrap::blade()->run('tasks.index', ['tasks' => $tasks, 'filter' => $filter]);
}
