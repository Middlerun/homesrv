<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class Task
{
    public static function find(int $id): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $stmt = $db->prepare('
            INSERT INTO tasks (title, description, created_by, assigned_to, next_due, due_time, recur_type, recur_interval, recur_days)
            VALUES (:title, :description, :created_by, :assigned_to, :next_due, :due_time, :recur_type, :recur_interval, :recur_days)
        ');
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'created_by' => $data['created_by'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'next_due' => $data['next_due'],
            'due_time' => $data['due_time'] ?: null,
            'recur_type' => $data['recur_type'] ?: null,
            'recur_interval' => $data['recur_interval'] ?: 1,
            'recur_days' => $data['recur_days'] ?: null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::get();
        $sets = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $sets[] = "$key = :$key";
            $params[$key] = $value;
        }
        $sets[] = "updated_at = datetime('now')";
        $sql = 'UPDATE tasks SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Get tasks, optionally filtered.
     * Filters: 'due' (due today or overdue), 'upcoming', 'completed', 'all'
     */
    public static function list(string $filter = 'all', ?int $assignedTo = null): array
    {
        $db = Database::get();
        $where = ['1=1'];
        $params = [];

        switch ($filter) {
            case 'due':
                $where[] = "next_due <= date('now')";
                $where[] = 'is_completed = 0';
                $where[] = 'is_paused = 0';
                break;
            case 'upcoming':
                $where[] = "next_due > date('now')";
                $where[] = 'is_completed = 0';
                $where[] = 'is_paused = 0';
                break;
            case 'completed':
                $where[] = 'is_completed = 1';
                break;
            case 'active':
                $where[] = 'is_completed = 0';
                $where[] = 'is_paused = 0';
                break;
            case 'all':
            default:
                break;
        }

        if ($assignedTo !== null) {
            $where[] = '(assigned_to = :assigned_to OR assigned_to IS NULL)';
            $params['assigned_to'] = $assignedTo;
        }

        $sql = 'SELECT tasks.*, users.name AS creator_name FROM tasks LEFT JOIN users ON tasks.created_by = users.id WHERE ' . implode(' AND ', $where) . ' ORDER BY next_due ASC, due_time ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get tasks for dashboard display: due or overdue, not completed/paused.
     */
    public static function dashboard(int $limit = 20): array
    {
        $db = Database::get();
        $stmt = $db->prepare("
            SELECT tasks.*, users.name AS assigned_name
            FROM tasks
            LEFT JOIN users ON tasks.assigned_to = users.id
            WHERE is_completed = 0 AND is_paused = 0
            ORDER BY next_due ASC, due_time ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function isRecurring(array $task): bool
    {
        return $task['recur_type'] !== null;
    }
}
