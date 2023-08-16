<?php

declare(strict_types = 1);

namespace Deondazy\Core\Config;

interface ConfigurationInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;
}