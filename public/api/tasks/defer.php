<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\User;
use HomeSrv\Services\TaskService;

Bootstrap::init();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'method not allowed']));
}

$taskId = (int) ($_POST['task_id'] ?? 0);
$newDue = $_POST['new_due'] ?? '';

if ($taskId <= 0 || $newDue === '') {
    http_response_code(400);
    die(json_encode(['error' => 'task_id and new_due required']));
}

// Use first user for task log attribution
$users = User::all();
$userId = !empty($users) ? (int) $users[0]['id'] : 1;

$updated = TaskService::defer($taskId, $userId, $newDue);

if ($updated === null) {
    http_response_code(404);
    die(json_encode(['error' => 'task not found']));
}

$today = date('Y-m-d');
$status = $updated['next_due'] < $today ? 'overdue' : ($updated['next_due'] === $today ? 'today' : 'future');

echo json_encode([
    'ok' => true,
    'task' => [
        'id' => (int) $updated['id'],
        'title' => $updated['title'],
        'next_due' => $updated['next_due'],
        'due_time' => $updated['due_time'] ? substr($updated['due_time'], 0, 5) : null,
        'recur_type' => $updated['recur_type'],
        'status' => $status,
    ],
]);
