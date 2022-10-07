<?php

require_once __DIR__ . '/../bootstrap.php';

use Deondazy\Core\Router;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$router = new Router;

// Load Twig template engine and pass it to the router
$loader = new FilesystemLoader(CORE_ROOT . '/views');
$twig = new Environment($loader, [
    'cache' => CORE_ROOT . '/cache',
    'debug' => true,
]);

require_once CORE_ROOT . '/routes/web.php';

$router->run();
