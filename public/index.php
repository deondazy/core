<?php

use Deondazy\Core\Router;

require_once __DIR__.'/../bootstrap/app.php';

$router = new Router;

require_once CORE_ROOT . '/routes/web.php';

$router->run();
