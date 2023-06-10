<?php 

declare(strict_types = 1);

use function Di\get;
use function DI\create;

use Slim\Views\Twig;
use Slim\Psr7\Response;
use Deondazy\Core\Config;
use DI\Bridge\Slim\Bridge;
use Doctrine\ORM\ORMSetup;
use Slim\Views\TwigMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Deondazy\Core\View\ViteExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Deondazy\App\Database\Entities\User;

use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use Deondazy\App\Services\UserAuthenticationService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

return [
    ServerRequestInterface::class => function () {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        return $serverRequestCreator->createServerRequestFromGlobals();
    },

    ResponseInterface::class => function () {
        return new Response();
    },

    'AppFactory' => function(ContainerInterface $container) {
        $factory = Bridge::create($container);

        $factory->addRoutingMiddleware();

        $factory->add(TwigMiddleware::createFromContainer($factory));

        $factory->add(new WhoopsMiddleware());

        return $factory;
    },

    EntityManager::class => function (Config $config) {
        return new EntityManager(
            DriverManager::getConnection($config->get('database.mysql')),
            ORMSetup::createAttributeMetadataConfiguration(
                $config->get('paths.entity_dir'),
                $config->get('app.debug')
            )
        );
    },

    Twig::class => function (Config $config) {
    
        $twig = Twig::create(
            $config->get('paths.views_dir'),
            $config->get('views.twig')
        );

        $twig->addExtension(new ViteExtension(
            $config->get('app.url'),
            $config->get('paths.public_dir') . '/build/manifest.json',
            $config->get('app.vite_server')
        ));
    
        // $twig->addExtension(new \Twig\Extension\DebugExtension());
    
        return $twig;
    },

    Config::class => function () {
        $directory = __DIR__ . '/../config/';
        
        $configs = [];
        foreach (glob($directory . '*.php') as $filename) {
            $key = pathinfo($filename, PATHINFO_FILENAME);
            $configs[$key] = require $filename;
        }
        
        return new Config($configs);
    },

    UserPasswordHasherInterface::class => function (ContainerInterface $container) {
        return $container->get(UserPasswordHasher::class);
    },

    UserPasswordHasher::class => function () {
        return new UserPasswordHasher(new PasswordHasherFactory([
            User::class => new NativePasswordHasher()
        ]));
    },

    UserAuthenticationService::class => function (ContainerInterface $container) {
        return new UserAuthenticationService(
            $container->get(EntityManager::class),
            $container->get(UserPasswordHasherInterface::class)
        );
    },
    
    'view' => get(Twig::class),
    'config' => get(Config::class)
];