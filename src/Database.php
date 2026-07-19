<?php

namespace HomeSrv;

use PDO;

class Database
{
    private static ?PDO $instance = null;
    private static ?string $overridePath = null;

    public static function setPath(string $path): void
    {
        self::$overridePath = $path;
        self::$instance = null;
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dbPath = self::$overridePath ?? __DIR__ . '/../database/homesrv.sqlite';
            self::$instance = new PDO('sqlite:' . $dbPath);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }
        return self::$instance;
    }
}
