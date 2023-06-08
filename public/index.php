<?php

declare(strict_types = 1);

$app = (require_once __DIR__ . '/../bootstrap/app.php')
    ->get('AppFactory');

require_once __DIR__ . '/../routes/web.php';

$app->run();
