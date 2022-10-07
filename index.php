<?php

require_once __DIR__ . '/bootstrap.php';

use Deondazy\Core\Router;

$router = new Router;

require_once __DIR__ . '/routes/web.php';

$router->run();
