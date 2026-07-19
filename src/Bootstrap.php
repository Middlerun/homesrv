<?php

namespace HomeSrv;

use eftec\bladeone\BladeOne;

class Bootstrap
{
    private static ?BladeOne $blade = null;

    public static function init(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        session_start();

        // Allow alternate database via ?_db= (test databases only)
        if (!empty($_GET['_db'])) {
            $name = basename($_GET['_db']);
            if (preg_match('/^[a-zA-Z0-9_-]+\.sqlite$/', $name) && $name !== 'homesrv.sqlite') {
                Database::setPath(__DIR__ . '/../database/' . $name);
            }
        }

        // Ensure DB exists
        Database::get();

        // Auth check
        Auth::check();
    }

    public static function blade(): BladeOne
    {
        if (self::$blade === null) {
            $views = __DIR__ . '/../views';
            $cache = __DIR__ . '/../cache';
            self::$blade = new BladeOne($views, $cache, BladeOne::MODE_DEBUG);
        }
        return self::$blade;
    }

    public static function isHtmx(): bool
    {
        return !empty($_SERVER['HTTP_HX_REQUEST']);
    }

    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
}
