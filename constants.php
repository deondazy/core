<?php 
/**
 * The Deondazy Core Constants definition File
 * 
 * This file contains all the constants used in the app
 * 
 * @package Deondazy\Core
 * @version 1.0.0
 */

// Define the app version
define('CORE_VERSION', '1.0.0');

// Define required PHP version
define('CORE_PHP', '7.4');

// Define installation root path
define('CORE_ROOT', __DIR__);

// Define Public root path
define('CORE_PUBLIC', CORE_ROOT . '/public');

// Define the app name
define('APP_NAME', $_ENV['APP_NAME']);

// Define the app url
define('APP_URL', $_ENV['APP_URL']);
