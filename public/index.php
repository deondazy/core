<?php

declare(strict_types = 1);

use DI\Bridge\Slim\Bridge;

// Require Autoloader
require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../bootstrap/app.php';

$app = Bridge::create($container);

require_once CORE_ROOT . '/routes/web.php';

$app->run();
