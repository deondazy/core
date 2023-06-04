<?php

declare(strict_types = 1);

// Require Autoloader
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

require_once CORE_ROOT . '/routes/web.php';

$app->run();
