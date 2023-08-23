<?php

declare(strict_types = 1);

use Slim\App;

error_reporting(E_ALL);
ini_set('display_errors', '1');

$app = (require_once __DIR__ . '/../bootstrap/app.php')
    ->get(App::class);

require_once __DIR__ . '/../routes/web.php';

$app->run();
