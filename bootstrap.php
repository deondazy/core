<?php
/**
 * The Deondazy Core Bootstrap File
 *
 */

// Define the app version
define('CORE_VERSION', '1.0.0');

// Define the DATABASE version
define('CORE_DB_VERSION', '1'); // Increment on every DB change.

// Define required PHP version
define('CORE_PHP', '7.4');

// Define installation root path
define('CORE_ROOT', dirname(__FILE__));

// Compare PHP versions against our required version
if (!version_compare(CORE_PHP, '7.4', '>=')) {
    exit(
        'Deondazy Core requires PHP ' . CORE_PHP . ' or higher, you currently have PHP ' . PHP_VERSION
    );
}

// Increase error reporting to E_ALL
error_reporting(E_ALL);

// Set default timezone, we'll base off of this later
// date_default_timezone_set('UTC');

// Require Autoloader
require_once CORE_ROOT . '/vendor/autoload.php';

// Use our own exception handler
//Deondazy\Core\Exception\OkoyeException::handle();

// Require the configuration file
require CORE_ROOT . '/config.php';

if ($config->debug->on) {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', $config->debug->logPath);
}
