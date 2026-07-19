<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;

Bootstrap::init();

$tasks = Task::dashboard();

echo Bootstrap::blade()->run('dashboard.partials.tasks', ['tasks' => $tasks]);
