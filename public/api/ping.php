<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;

Bootstrap::init();

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
