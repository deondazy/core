<?php

use Deondazy\Core\Controllers\ContactController;

$router->get('/', function() {
    echo 'Hello World';
});

$router->get('/about', function() {
    echo 'About Page';
});

$router->get('/contact', ContactController::class . '@index');

$router->notFoundHandler(function() {
    require_once __DIR__ . '/../views/404.php';
});
