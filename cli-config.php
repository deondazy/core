<?php 

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

require_once __DIR__ . '/bootstrap/database.php';

return DependencyFactory::fromEntityManager(
    new PhpFile('migrations.php'), 
    new ExistingEntityManager($entityManager)
);
