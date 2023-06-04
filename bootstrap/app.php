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
use DI\Bridge\Slim\Bridge;
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

return Bridge::create($container);
