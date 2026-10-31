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

    /** Where a database of this name lives. */
    public static function pathFor(string $name): string
    {
        return __DIR__ . '/../database/' . $name;
    }

    /** The database this request is using. */
    public static function path(): string
    {
        return self::$overridePath ?? self::pathFor('homesrv.sqlite');
    }

    public static function get(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO('sqlite:' . self::path());
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }
        return self::$instance;
    }
}
