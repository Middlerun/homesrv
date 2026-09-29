<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Services\DashboardService;

Bootstrap::init();

header('Content-Type: application/json');

echo json_encode(DashboardService::data(), JSON_UNESCAPED_UNICODE);
