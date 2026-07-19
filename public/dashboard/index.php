<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Bootstrap;

Bootstrap::init();

echo Bootstrap::blade()->run('dashboard.index');
