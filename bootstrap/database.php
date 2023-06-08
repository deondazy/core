<?php 

use Deondazy\Core\Config;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

$app = require_once __DIR__ . '/app.php';

$paths = [__DIR__ . '/../src/Database/Entities'];
$isDevMode = false;

$dbParams = $app->get('config')->get('database.mysql');

$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
$connection = DriverManager::getConnection($dbParams, $config);
$entityManager = new EntityManager($connection, $config);
