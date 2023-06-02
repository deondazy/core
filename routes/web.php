<?php

use Deondazy\App\Controllers\AuthController;
use Deondazy\App\Controllers\HomeController;

$app->get('/', [HomeController::class, 'index']);

$app->get('/sign-in', [AuthController::class, 'login']);
$app->get('/sign-up', [AuthController::class, 'register']);
