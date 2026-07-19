#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HomeSrv\Database;

$dbName = $argv[1] ?? 'homesrv.sqlite';
$dbPath = __DIR__ . '/../database/' . $dbName;

if ($dbName !== 'homesrv.sqlite') {
    Database::setPath($dbPath);
}

echo "Setting up database: $dbName\n";

$db = Database::get();
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
$db->exec($schema);

echo "Database created at: " . realpath($dbPath) . "\n";
echo "Done.\n";
