<?php 

declare(strict_types = 1);

use Slim\Views\Twig;
use Slim\Psr7\Response;
use Deondazy\Core\Config;
use DI\Bridge\Slim\Bridge;
use Doctrine\ORM\ORMSetup;
use Slim\Views\TwigMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;
use Deondazy\Core\View\ViteExtension;
use Psr\Http\Message\ResponseInterface;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

use function Di\get;

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

    Twig::class => function (ContainerInterface $container) {
        dd($container->get(Config::class)->get('app.debug'));
        $config = $container->get(Config::class)->get('views.twig');
    
        $twig = Twig::create(__DIR__ . '/../app/Views', $config);

        $twig->addExtension(new ViteExtension(
            $container->get(Config::class)->get('app.debug'),
            [],
            $container->get(Config::class)
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
    
    'view' => get(Twig::class),
    'config' => get(Config::class)
];