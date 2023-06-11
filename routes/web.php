<?php

use Deondazy\App\Controllers\AuthController;
use Deondazy\App\Controllers\HomeController;
use Deondazy\App\Controllers\DashboardController;
use Deondazy\App\Middleware\RequireAuthentication;

$app->get('/', [HomeController::class, 'index'])->setName('home');

$app->get('/login', [AuthController::class, 'showLoginForm'])->setName('login');
$app->post('/login', [AuthController::class, 'login'])->setName('login.post');
$app->get('/register', [AuthController::class, 'showRegistrationForm'])->setName('register');
$app->post('/register', [AuthController::class, 'register'])->setName('register.post');
$app->get('/forgot-password', [AuthController::class, 'forgotPassword'])->setName('forgot-password');

$app->group('/dashboard', function ($app) {
    $app->get('', [DashboardController::class, 'index'])->setName('dashboard.index');
})->add(RequireAuthentication::class);
