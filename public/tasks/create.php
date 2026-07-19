<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;
use HomeSrv\Models\Task;
use HomeSrv\Models\TaskLog;
use HomeSrv\Models\User;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recurDays = null;
    if (!empty($_POST['recur_days_list']) && is_array($_POST['recur_days_list'])) {
        $recurDays = implode(',', array_map('intval', $_POST['recur_days_list']));
    }

    $taskId = Task::create([
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description'] ?? ''),
        'created_by' => Auth::userId(),
        'assigned_to' => $_POST['assigned_to'] ?: null,
        'next_due' => $_POST['next_due'],
        'due_time' => $_POST['due_time'] ?: null,
        'recur_type' => $_POST['recur_type'] ?: null,
        'recur_interval' => (int) ($_POST['recur_interval'] ?? 1),
        'recur_days' => $recurDays,
    ]);

    TaskLog::log($taskId, Auth::userId(), 'created');
    Bootstrap::redirect('/tasks/');
    exit;
}

$users = User::all();
echo Bootstrap::blade()->run('tasks.create', ['users' => $users]);
