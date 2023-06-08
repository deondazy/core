<?php

declare(strict_types = 1);

use DI\Bridge\Slim\Bridge;
use Slim\Views\TwigMiddleware;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

ini_set('display_errors', true);

$container = require_once __DIR__ . '/../bootstrap/app.php';

$app = Bridge::create($container);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

$app->add(new WhoopsMiddleware());

require_once __DIR__ . '/../routes/web.php';

$app->run();
