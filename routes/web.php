<?php

use Deondazy\App\Controllers\HomeController;

$router->get('/', HomeController::class . '@index');

$router->notFoundHandler(function() {
    require_once CORE_ROOT . '/app/Views/404.html';
});
