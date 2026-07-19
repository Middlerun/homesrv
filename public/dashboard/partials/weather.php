<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use HomeSrv\Bootstrap;
use HomeSrv\Services\WeatherService;

Bootstrap::init();

$weather = WeatherService::get();

echo Bootstrap::blade()->run('dashboard.partials.weather', ['weather' => $weather]);
