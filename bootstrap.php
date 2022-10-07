<?php
/**
 * The Deondazy Core Bootstrap File
 * 
 * This file sets up all the required files and constants
 * 
 * @package Deondazy\Core
 * @version 1.0.0
 */

// File cannot be accessed directly
// if (!defined('CORE_ROOT')) {
//     header('HTTP/1.0 403 Forbidden');
//     exit;
// }

// Require the constants file
require_once __DIR__ . '/constants.php';

// Require Autoloader
require_once CORE_ROOT . '/vendor/autoload.php';

// Use Whoops for error handling
$whoops = new Whoops\Run;
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
$whoops->register();

// We need to check if the PHP version is compatible with the app
if (version_compare(PHP_VERSION, CORE_PHP, '<')) {
    throw new Deondazy\Core\Exceptions\InvalidRequirementException(
        'PHP version ' . CORE_PHP . ' or higher is required. You are using PHP version ' . PHP_VERSION
    );
}

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(CORE_ROOT);
$dotenv->safeLoad();

// Load Database connection
$database = Deondazy\Core\Database::instance()->connect("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
