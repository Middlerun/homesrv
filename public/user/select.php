<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;
use HomeSrv\Models\User;

Bootstrap::init();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    if (Auth::selectUser($userId)) {
        Bootstrap::redirect('/');
    }
    Bootstrap::redirect('/user/select.php');
}

$users = User::all();
echo Bootstrap::blade()->run('user-select', ['users' => $users]);
