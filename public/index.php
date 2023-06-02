<?php

use Slim\Factory\AppFactory;

require_once __DIR__ . '/../bootstrap/app.php';

$app = AppFactory::create();

require_once CORE_ROOT . '/routes/web.php';

$app->run();
