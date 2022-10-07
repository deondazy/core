<?php

use Deondazy\Core\Controllers\HomepageController;

$router->get('/', HomepageController::class . '@index');

$router->notFoundHandler(function() {
    require_once __DIR__ . '/../views/404.html';
});
