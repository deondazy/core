<?php

declare(strict_types = 1);

use Slim\App;

ini_set('display_errors', true);

$app = (require_once __DIR__ . '/../bootstrap/app.php')
    ->get(App::class);

require_once __DIR__ . '/../routes/web.php';

$app->run();
