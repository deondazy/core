<?php

use Deondazy\App\Controllers\AuthController;
use Deondazy\App\Controllers\HomeController;

$app->get('/', [HomeController::class, 'index'])->setName('home');

$app->get('/sign-in/{username}', [AuthController::class, 'login'])->setName('login');
$app->get('/register', [AuthController::class, 'register'])->setName('register');
