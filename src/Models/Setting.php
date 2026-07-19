<?php

namespace HomeSrv\Models;

use HomeSrv\Database;

class Setting
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        $db = Database::get();
        $stmt = $db->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        $stmt->execute([$key, $value]);
    }

    public static function all(): array
    {
        $db = Database::get();
        $stmt = $db->query('SELECT key, value FROM settings');
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }
}
