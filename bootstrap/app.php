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
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

// Require Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
Dotenv::createImmutable(dirname(dirname(__FILE__)))->safeLoad();

// Require the constants file
require_once __DIR__ . '/constants.php';

// Use Whoops for error handling
$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();

// Check if the current PHP version is compatible with the app
if (version_compare(PHP_VERSION, CORE_PHP, '<')) {
    throw new Deondazy\Core\Exceptions\InvalidRequirementException(
        'PHP version ' . CORE_PHP . ' or higher is required. You are using PHP version ' . PHP_VERSION
    );
}
