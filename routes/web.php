<?php

use Denosys\App\Controllers\AuthController;
use Denosys\App\Controllers\HomeController;
use Denosys\App\Controllers\DashboardController;
use Denosys\App\Middleware\RequireAuthentication;

$app->get('/', [HomeController::class, 'index'])->setName('home');

$app->get('/login', [AuthController::class, 'showLoginForm'])->setName('login');
$app->post('/login', [AuthController::class, 'login'])->setName('login.post');
$app->get('/register', [AuthController::class, 'showRegistrationForm'])->setName('register');
$app->post('/register', [AuthController::class, 'register'])->setName('register.post');
$app->get('/forgot-password', [AuthController::class, 'forgotPassword'])->setName('forgot-password');
$app->post('/logout', [AuthController::class, 'logout'])->setName('logout');

$app->group('/dashboard', function ($app) {
    $app->get('', [DashboardController::class, 'index'])->setName('dashboard.index');
    $app->get('/profile', [DashboardController::class, 'profile'])->setName('dashboard.profile');
})->add(RequireAuthentication::class);
