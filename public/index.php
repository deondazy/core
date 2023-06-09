<?php

declare(strict_types = 1);

ini_set('display_errors', true);

$app = (require_once __DIR__ . '/../bootstrap/app.php')
    ->get('AppFactory');

require_once __DIR__ . '/../routes/web.php';

$app->run();
