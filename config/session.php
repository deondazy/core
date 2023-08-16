<?php

declare(strict_types = 1);

return [
    'name' => 'core_web_session',
    'lifetime' => 7200,
    'path' => '/',
    'domain' => null,
    'secure' => true,
    'httponly' => true,
    'cache_limiter' => 'nocache',
    'same_site' => 'lax',
];
