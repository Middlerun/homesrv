<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class TaskLog
{
    public static function log(int $taskId, int $userId, string $action, ?string $note = null, ?string $oldDue = null, ?string $newDue = null): void
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT INTO task_log (task_id, user_id, action, note, old_due, new_due) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$taskId, $userId, $action, $note, $oldDue, $newDue]);
    }

    public static function forTask(int $taskId, int $limit = 20): array
    {
        $db = Database::get();
        $stmt = $db->prepare('
            SELECT task_log.*, users.name AS user_name
            FROM task_log
            LEFT JOIN users ON task_log.user_id = users.id
            WHERE task_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$taskId, $limit]);
        return $stmt->fetchAll();
    }
}
