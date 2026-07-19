<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HomeSrv\Auth;
use HomeSrv\Bootstrap;

Bootstrap::init();
Auth::logout();
Bootstrap::redirect('/login.php');
