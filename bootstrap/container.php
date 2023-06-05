<?php 

declare(strict_types = 1);

use Slim\Views\Twig;
use Slim\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

return [
    ServerRequestInterface::class => function () {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        return $serverRequestCreator->createServerRequestFromGlobals();
    },

    ResponseInterface::class => function () {
        return new Response();
    },

    Twig::class => function (ContainerInterface $container) {
        $config = $container->get(Config::class)->get('views.twig');
    
        $twig = Twig::create(__DIR__ . '/../app/Views', $config);
    
        // $twig->addExtension(new \Twig\Extension\DebugExtension());
    
        return $twig;
    },

    'view' => function (ContainerInterface $container) {
        return $container->get(Twig::class);
    },
];