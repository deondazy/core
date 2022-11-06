<?php

namespace Deondazy\Core\Config;

use Deondazy\Core\Config\Exceptions\FileNotFoundException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlConfig
{
    /**
     * Check that the Yaml configuration file exists
     *
     * @param string $file
     *
     * @return bool
     *
     * @throws FileNotFoundException
     */
    private function checkFile(string $file): bool
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException("File \"{$file}\" does not exist");
        }

        return true;
    }

    /**
     * Get the Yaml configuration file
     *
     * @param string $yamlFile
     *
     * @return array
     *
     * @throws ParseException
     * @throws FileNotFoundException
     */
    public function get(string $yamlFile): array
    {
        foreach (glob(CORE_CONFIG . DS . '*.yaml') as $file) {
            $this->checkFile($file);
            $parts = parse_url($file);
            $path = $parts['path'];

            if (strpos($path, $yamlFile) !== false) {
                return Yaml::parseFile($file);
            }
        }

        throw new FileNotFoundException("File \"{$yamlFile}\" does not exist");
    }

    /**
     * Load the yaml configuration into the yaml parser
     *
     * @param string $yamlFile
     *
     * @return array
     * @throws FileNotFoundException
     */
    public static function load(string $yamlFile): array
    {
        return (new YamlConfig)->get($yamlFile);
    }
}
