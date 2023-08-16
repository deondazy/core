<?php

declare(strict_types=1);

namespace Deondazy\Core\Config;

use Deondazy\Core\Config\ConfigurationInterface;
use Deondazy\Core\Environment\EnvironmentLoaderInterface;

class ArrayFileConfiguration implements ConfigurationInterface
{
    public function __construct(
        private EnvironmentLoaderInterface $envLoader,
        private readonly array $config
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path  = explode('.', $key);
        $value = $this->config;

        foreach ($path as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }

        // If value starts with 'env:', it will read from the .env file
        if (is_string($value) && str_starts_with($value, 'env:')) {
            return $this->loadEnvironmentValue($value);
        }

        return $value;
    }

    private function loadEnvironmentValue(string $value): mixed
    {
        if (strpos($value, 'env:') === 0) {
            $matches = [];
            if (preg_match('/^env:([a-zA-Z0-9_]+),(.*)$/', $value, $matches)) {
                $envKey = $matches[1];
                $defaultValue = trim($matches[2]);
                return $this->envLoader->get($envKey, $defaultValue);
            }
        }

        return $value;
    }
}
