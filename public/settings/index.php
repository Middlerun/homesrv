<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;
use HomeSrv\Models\CalendarFeed;
use HomeSrv\Models\Setting;
use HomeSrv\Models\User;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'set_password':
            $password = $_POST['password'] ?? '';
            if (strlen($password) >= 1) {
                Setting::set('app_password_hash', password_hash($password, PASSWORD_DEFAULT));
                Bootstrap::flash('success', 'Password saved.');
                if (empty($_SESSION['authenticated'])) {
                    Auth::markAuthenticated();
                }
            }
            break;

        case 'add_user':
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                User::create($name);
                Bootstrap::flash('success', "Added $name.");
            }
            break;

        case 'delete_user':
            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId > 0) {
                User::delete($userId);
                Bootstrap::flash('success', 'User removed.');
            }
            break;

        case 'add_feed':
            $name = trim($_POST['feed_name'] ?? '');
            $url = trim($_POST['feed_url'] ?? '');
            $color = $_POST['feed_color'] ?? '#3b82f6';
            if ($name && $url) {
                CalendarFeed::create($name, $url, $color);
                Bootstrap::flash('success', "Added feed: $name.");
            }
            break;

        case 'delete_feed':
            $feedId = (int) ($_POST['feed_id'] ?? 0);
            if ($feedId > 0) {
                CalendarFeed::delete($feedId);
                Bootstrap::flash('success', 'Feed removed.');
            }
            break;

        case 'toggle_feed':
            $feedId = (int) ($_POST['feed_id'] ?? 0);
            if ($feedId > 0) {
                CalendarFeed::toggleEnabled($feedId);
                Bootstrap::flash('success', 'Feed updated.');
            }
            break;

        case 'save_weather':
            Setting::set('weather_latitude', trim($_POST['weather_latitude'] ?? ''));
            Setting::set('weather_longitude', trim($_POST['weather_longitude'] ?? ''));
            Setting::set('weather_units', $_POST['weather_units'] ?? 'celsius');
            Bootstrap::flash('success', 'Weather settings saved.');
            break;

        case 'save_timezone':
            Setting::set('timezone', trim($_POST['timezone'] ?? 'UTC'));
            Bootstrap::flash('success', 'Timezone saved.');
            break;

        case 'regenerate_token':
            Setting::set('api_token', bin2hex(random_bytes(16)));
            Bootstrap::flash('success', 'API token regenerated.');
            break;
    }

    Bootstrap::redirect('/settings/');
}

$hasPassword = Setting::get('app_password_hash') !== null;
$users = User::all();
$feeds = CalendarFeed::all();
$settings = Setting::all();

// Ensure API token exists
if (empty($settings['api_token'])) {
    $settings['api_token'] = bin2hex(random_bytes(16));
    Setting::set('api_token', $settings['api_token']);
}

$success = Bootstrap::getFlash('success');

echo Bootstrap::blade()->run('settings.index', [
    'hasPassword' => $hasPassword,
    'users' => $users,
    'feeds' => $feeds,
    'settings' => $settings,
    'success' => $success,
]);
