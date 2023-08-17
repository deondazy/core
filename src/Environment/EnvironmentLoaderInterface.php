<?php

declare(strict_types = 1);

namespace Denosys\Core\Environment;

interface EnvironmentLoaderInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);
}