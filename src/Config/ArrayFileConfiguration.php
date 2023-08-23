<?php

declare(strict_types=1);

namespace Denosys\Core\Config;

use Denosys\Core\Config\ConfigurationInterface;

class ArrayFileConfiguration implements ConfigurationInterface
{
    public function __construct(
        private readonly array $config
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path  = explode('.', $key);
        $value = $this->config;

        foreach ($path as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                $value = $default;
            }
        }

        return $value;
    }
}
