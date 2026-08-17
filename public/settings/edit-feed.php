<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Models\CalendarFeed;

Bootstrap::init();

$feedId = (int) ($_GET['feed_id'] ?? $_POST['feed_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $feed = CalendarFeed::find($feedId);
    if ($feed) {
        echo Bootstrap::blade()->run('components.feed-edit-modal', ['feed' => $feed]);
    }
    exit;
}

$name = trim($_POST['feed_name'] ?? '');
$color = $_POST['feed_color'] ?? '';
if ($feedId > 0 && $name !== '' && $color !== '') {
    CalendarFeed::update($feedId, $name, $color);
    Bootstrap::flash('success', 'Feed updated.');
}

Bootstrap::redirect('/settings/');
