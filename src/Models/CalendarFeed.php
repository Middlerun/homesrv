<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class CalendarFeed
{
    public static function all(): array
    {
        $db = Database::get();
        return $db->query('SELECT * FROM calendar_feeds ORDER BY name')->fetchAll();
    }

    public static function enabled(): array
    {
        $db = Database::get();
        return $db->query('SELECT * FROM calendar_feeds WHERE is_enabled = 1 ORDER BY name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM calendar_feeds WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, string $url, string $color = '#3b82f6'): int
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT INTO calendar_feeds (name, url, color) VALUES (?, ?, ?)');
        $stmt->execute([trim($name), trim($url), $color]);
        return (int) $db->lastInsertId();
    }

    public static function updateCache(int $id, string $data): void
    {
        $db = Database::get();
        $stmt = $db->prepare("UPDATE calendar_feeds SET cached_data = ?, cached_at = datetime('now') WHERE id = ?");
        $stmt->execute([$data, $id]);
    }

    public static function delete(int $id): void
    {
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM calendar_feeds WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function update(int $id, string $name, string $color): void
    {
        $db = Database::get();
        $stmt = $db->prepare('UPDATE calendar_feeds SET name = ?, color = ? WHERE id = ?');
        $stmt->execute([trim($name), $color, $id]);
    }

    public static function toggleEnabled(int $id): void
    {
        $db = Database::get();
        $stmt = $db->prepare('UPDATE calendar_feeds SET is_enabled = 1 - is_enabled WHERE id = ?');
        $stmt->execute([$id]);
    }
}
