<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (Auth::login($password)) {
        Bootstrap::redirect('/user/select.php');
    } else {
        Bootstrap::flash('error', 'Incorrect password.');
        Bootstrap::redirect('/login.php');
    }
}

echo Bootstrap::blade()->run('login', ['error' => Bootstrap::getFlash('error')]);
