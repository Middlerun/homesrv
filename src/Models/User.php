<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class User
{
    public static function all(): array
    {
        $db = Database::get();
        return $db->query('SELECT * FROM users ORDER BY name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name): int
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT INTO users (name) VALUES (?)');
        $stmt->execute([trim($name)]);
        return (int) $db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $db = Database::get();

        // Nullify nullable references
        $db->prepare('UPDATE tasks SET assigned_to = NULL WHERE assigned_to = ?')->execute([$id]);

        // Temporarily disable FK checks to allow deleting a user
        // whose id is still referenced by tasks.created_by (NOT NULL) and task_log.user_id (NOT NULL).
        // The orphaned IDs are harmless — queries JOIN users with LEFT JOIN.
        $db->exec('PRAGMA foreign_keys=OFF');
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $db->exec('PRAGMA foreign_keys=ON');
    }
}
