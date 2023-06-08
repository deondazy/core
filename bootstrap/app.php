<?php

/**
 * The Deondazy Core Bootstrap File
 *
 * This file sets up all the required files and constants
 *
 * @package Deondazy\Core
 * @version 1.0.0
 */

use Dotenv\Dotenv;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
Dotenv::createImmutable(dirname(dirname(__FILE__)))->safeLoad();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');
return $containerBuilder->build();
