<?php

declare(strict_types=1);

return [

    'mysql' => [
        'driver'   => 'pdo_mysql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'dbname'   => $_ENV['DB_NAME'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset'  => 'utf8mb4',
    ],
];
