<?php

/**
 * The Deondazy Core Bootstrap File
 *
 * This file sets up all the required files and constants
 *
 * @package Deondazy\Core
 * @version 1.0.0
 */

use Whoops\Run;
use Dotenv\Dotenv;
use DI\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;
use Whoops\Handler\PrettyPageHandler;

// Load environment variables
Dotenv::createImmutable(dirname(dirname(__FILE__)))->safeLoad();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

// Require the constants file
require_once __DIR__ . '/constants.php';

// Require the config file
$config = Yaml::parseFile(CORE_CONFIG . '/app.yaml');

// Use Whoops for error handling if debug is enabled
if ($config['debug']) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler());
    $whoops->register();
}

// Check if the current PHP version is compatible with the app
if (version_compare(PHP_VERSION, CORE_PHP, '<')) {
    throw new Deondazy\Core\Exceptions\InvalidRequirementException(
        'PHP version ' . CORE_PHP . ' or higher is required. You are using PHP version ' . PHP_VERSION
    );
}
