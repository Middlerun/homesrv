<?php

namespace HomeSrv\Services;

use HomeSrv\Models\Task;
use HomeSrv\Models\TaskLog;

class TaskService
{
    public static function complete(int $taskId, int $userId): ?array
    {
        $task = Task::find($taskId);
        if (!$task) return null;

        $oldDue = $task['next_due'];

        if (Task::isRecurring($task)) {
            $newDue = RecurrenceCalculator::advance($task);
            Task::update($taskId, ['next_due' => $newDue]);
            TaskLog::log($taskId, $userId, 'completed', null, $oldDue, $newDue);
            return Task::find($taskId);
        } else {
            Task::update($taskId, ['is_completed' => 1]);
            TaskLog::log($taskId, $userId, 'completed', null, $oldDue, null);
            return Task::find($taskId);
        }
    }

    public static function defer(int $taskId, int $userId, string $newDue): ?array
    {
        $task = Task::find($taskId);
        if (!$task) return null;

        $oldDue = $task['next_due'];
        Task::update($taskId, ['next_due' => $newDue]);
        TaskLog::log($taskId, $userId, 'deferred', null, $oldDue, $newDue);

        return Task::find($taskId);
    }
}
