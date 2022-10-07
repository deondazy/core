<?php
/**
 * The Deondazy Core Bootstrap File
 *
 */

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Deondazy\Core\Exceptions\InvalidRequirementException;

// Define the app version
define('CORE_VERSION', '1.0.0');

// Define required PHP version
define('CORE_PHP', '7.4');

// Define installation root path
define('CORE_ROOT', __DIR__);

// Require Autoloader
require_once CORE_ROOT . '/vendor/autoload.php';

// Use Whoops for error handling
$whoops = new Run;
$whoops->pushHandler(new PrettyPageHandler);
$whoops->register();

// We need to check if the PHP version is compatible with the app
if (version_compare(PHP_VERSION, CORE_PHP, '<')) {
    throw new InvalidRequirementException('PHP version ' . CORE_PHP . ' or higher is required. You are using PHP version ' . PHP_VERSION);
}
