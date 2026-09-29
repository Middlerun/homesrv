<?php

/**
 * Render a Kobo UI tree on the command line.
 *
 * Same builder as /api/ui.php, without the web server — which is what keeps
 * `make -C kobo preview` and the golden layout tests working from a fixture,
 * with no server and no device:
 *
 *   php bin/render-ui.php kobo/fixtures/typical.json --width 758
 *   php bin/render-ui.php --live --width 1072 --pretty
 *
 * The output is the same envelope the endpoint returns, on stdout.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use HomeSrv\Services\DashboardService;
use HomeSrv\Ui\DashboardScreen;
use HomeSrv\Ui\Screen;

$args = array_slice($argv, 1);
$width = 758;
$live = false;
$pretty = false;
$path = null;

for ($i = 0; $i < count($args); $i++) {
    $arg = $args[$i];
    $val = $args[$i + 1] ?? null;
    if ($arg === '--width' && $val !== null) {
        $width = (int) $val;
        $i++;
    } elseif ($arg === '--live') {
        $live = true;
    } elseif ($arg === '--pretty') {
        $pretty = true;
    } elseif ($arg === '--help' || $arg === '-h') {
        fwrite(STDERR, "usage: render-ui.php [FIXTURE.json|-] [--live] [--width N] [--pretty]\n");
        exit(2);
    } elseif ($path === null && !str_starts_with($arg, '--')) {
        $path = $arg;
    } else {
        fwrite(STDERR, "render-ui.php: unexpected argument '$arg'\n");
        exit(2);
    }
}

if ($live) {
    $data = DashboardService::data();
    $tz = DashboardService::timezone();
} else {
    if ($path === null) {
        fwrite(STDERR, "render-ui.php: give a fixture path, '-' for stdin, or --live\n");
        exit(2);
    }
    $raw = $path === '-' ? stream_get_contents(STDIN) : @file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, "render-ui.php: cannot read $path\n");
        exit(1);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        fwrite(STDERR, "render-ui.php: $path is not valid JSON\n");
        exit(1);
    }
    // Fixtures carry their own dates, so the timezone only has to be stable.
    $tz = new DateTimeZone('UTC');
}

$screen = new DashboardScreen($data, $width, $tz);

$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
if ($pretty) {
    $flags |= JSON_PRETTY_PRINT;
}
echo json_encode(Screen::envelope($screen->build()), $flags), "\n";
