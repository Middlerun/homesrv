<?php

/**
 * The Kobo dashboard as a UI tree.
 *
 * The device asks for a screen, gets a tree of boxes and text, and lays it out
 * and paints it. Everything about what the dashboard *looks like* is decided
 * here, so iterating on it does not mean reinstalling the client.
 *
 *   GET /api/ui.php?w=758&h=1024&dpi=213
 *
 * Only the width is used (spacing scales with it); height and dpi are accepted
 * so the client can report its panel without the wire format changing later.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Services\DashboardService;
use HomeSrv\Ui\DashboardScreen;
use HomeSrv\Ui\Screen;

Bootstrap::init();

header('Content-Type: application/json');

$width = (int) ($_GET['w'] ?? 758);
if ($width < 200 || $width > 4000) {
    $width = 758;
}

$screen = new DashboardScreen(DashboardService::data(), $width,
    DashboardService::timezone());

echo json_encode(Screen::envelope($screen->build()), JSON_UNESCAPED_UNICODE);
