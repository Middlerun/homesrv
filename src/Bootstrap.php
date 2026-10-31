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

        if (isset($_GET['_db'])) {
            self::useTestDatabase((string) $_GET['_db']);
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

    /**
     * Serve this request from a test database instead (?_db=, see AGENTS.md).
     *
     * A development aid, so it is off unless the server opts in with
     * HOMESRV_ALLOW_TEST_DB=1 — otherwise any visitor could point a request at
     * a database whose password they control. It only opens a database that
     * already exists (opening a missing SQLite file creates it), and it refuses
     * rather than quietly falling back to the real database, so a test run can
     * never land on real data because the switch was off.
     */
    private static function useTestDatabase(string $requested): void
    {
        $allowed = ($_SERVER['HOMESRV_ALLOW_TEST_DB'] ?? getenv('HOMESRV_ALLOW_TEST_DB')) === '1';
        $name = basename($requested);

        if (!$allowed
            || !preg_match('/^[a-zA-Z0-9_-]+\.sqlite$/', $name)
            || $name === 'homesrv.sqlite'
            || !is_file(Database::pathFor($name))) {
            http_response_code(404);
            header('Content-Type: text/plain');
            exit("Test database not available.\n");
        }
        Database::setPath(Database::pathFor($name));
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
