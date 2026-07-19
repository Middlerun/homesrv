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

        // Auth check — API routes use token, web routes use session
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (str_starts_with($path, '/api/')) {
            Auth::checkApiToken();
        } else {
            Auth::check();
        }
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
