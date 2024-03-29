<?php

declare(strict_types=1);

namespace Denosys\Core\Config;

class ConfigurationManager
{
    public function loadConfigurationFiles(string $directory): array
    {
        $config = [];

        foreach ($this->findConfigurationFiles($directory) as $filename) {
            try {
                $key = pathinfo($filename, PATHINFO_FILENAME);
                $configFile = require_once $directory . $filename;

                $config[$key] = $configFile;
            } catch (\Throwable $e) {
                // Log an error message and continue with the next file
                error_log(sprintf('Error loading configuration file %s: %s', $filename, $e->getMessage()));
            }
        }

        return $config;
    }

    private function findConfigurationFiles(string $directory): array
    {
        return array_filter(scandir($directory), function (string $filename) {
            return pathinfo($filename, PATHINFO_EXTENSION) === 'php';
        });
    }
}
