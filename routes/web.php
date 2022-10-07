<?php

use Deondazy\Core\Controllers\HomepageController;

$router->get('/', HomepageController::class . '@index');

$router->notFoundHandler(function() {
    require_once CORE_ROOT . '/views/404.html';
});

// TODO: Remove this
$router->get('/test', function() {
    require_once CORE_ROOT . '/test.php';
});