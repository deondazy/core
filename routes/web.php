<?php

use Deondazy\App\Controllers\AuthController;
use Deondazy\App\Controllers\HomeController;

$app->get('/', [HomeController::class, 'index'])->setName('home');

$app->get('/login', [AuthController::class, 'login'])->setName('login');
$app->get('/register', [AuthController::class, 'showRegistrationForm'])->setName('register');
$app->post('/register', [AuthController::class, 'register'])->setName('register.post');
$app->get('/forgot-password', [AuthController::class, 'forgotPassword'])->setName('forgot-password');
