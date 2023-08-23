<?php

declare(strict_types=1);

namespace Denosys\Core\Support;

use Denosys\Core\Environment\EnvironmentLoaderInterface;

class Env
{
    private static EnvironmentLoaderInterface $loader;

    public static function setLoader(EnvironmentLoaderInterface $loader): void
    {
        self::$loader = $loader;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$loader->get($key, $default);
    }
}
