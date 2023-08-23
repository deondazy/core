<?php

declare(strict_types=1);

use Denosys\Core\Support\Env;

if (!function_exists('app')) {
    function app(string $abstract = null): mixed
    {
        if (is_null($abstract)) {
            return \Denosys\Core\Application::getInstance();
        }

        return \Denosys\Core\Application::getInstance()->get($abstract);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        Env::setLoader(app()->get(\Denosys\Core\Environment\EnvironmentLoaderInterface::class));
        return Env::get($key, $default);
    }
}
