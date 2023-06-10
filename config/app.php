<?php 

declare(strict_types = 1);

return [

    'name' => $_ENV['APP_NAME'] ?? 'Core Web',
    'version' => $_ENV['APP_VERSION'],
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => false,
];