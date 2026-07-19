#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HomeSrv\Services\CalendarService;

$count = CalendarService::fetchAll();
echo "Fetched $count calendar feed(s).\n";
