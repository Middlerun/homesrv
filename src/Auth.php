<?php

namespace HomeSrv;

use HomeSrv\Models\Setting;
use HomeSrv\Models\User;

class Auth
{
    public static function check(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $isSettings = str_starts_with($path, '/settings');
        $passwordHash = Setting::get('app_password_hash');

        // First run — no password set yet, redirect to settings
        if ($passwordHash === null) {
            if (!$isSettings) {
                Bootstrap::redirect('/settings/');
            }
            return;
        }

        // Not authenticated — redirect to login
        if (empty($_SESSION['authenticated'])) {
            if ($path !== '/login.php') {
                Bootstrap::redirect('/login.php');
            }
            return;
        }

        // Authenticated but no user selected — allow settings access
        if (empty($_SESSION['user_id'])) {
            if ($path !== '/user/select.php' && !$isSettings) {
                Bootstrap::redirect('/user/select.php');
            }
            return;
        }
    }

    public static function login(string $password): bool
    {
        $hash = Setting::get('app_password_hash');
        if ($hash && password_verify($password, $hash)) {
            $_SESSION['authenticated'] = true;
            return true;
        }
        return false;
    }

    public static function selectUser(int $userId): bool
    {
        $user = User::find($userId);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        session_destroy();
    }

    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function userName(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }
}
