<?php

declare(strict_types=1);

use Slim\App;
use function Di\get;
use Slim\Views\Twig;
use Slim\Psr7\Response;
use Deondazy\Core\Config;
use DI\Bridge\Slim\Bridge;
use Doctrine\ORM\ORMSetup;
use Odan\Session\PhpSession;
use Slim\Views\TwigMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Odan\Session\SessionInterface;
use Deondazy\Core\View\ViteExtension;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Deondazy\App\Database\Entities\User;
use Odan\Session\SessionManagerInterface;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;
use Deondazy\App\Middleware\RequireAuthentication;
use Odan\Session\Middleware\SessionStartMiddleware;
use Deondazy\App\Services\UserAuthenticationService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;

return [
    App::class => function (ContainerInterface $container) {
        $app = Bridge::create($container);
        
        $app->addRoutingMiddleware();
        
        $app->add(SessionStartMiddleware::class);
        $app->add(TwigMiddleware::createFromContainer($app));
        $app->add(new WhoopsMiddleware());

        return $app;
    },

    ResponseInterface::class => fn () => new Response(),

    EntityManager::class => function (Config $config) {
        return new EntityManager(
            DriverManager::getConnection($config->get('database.mysql')),
            ORMSetup::createAttributeMetadataConfiguration(
                $config->get('paths.entity_dir'),
                $config->get('app.debug')
            )
        );
    },

    SessionManagerInterface::class => fn (ContainerInterface $container) 
        => $container->get(SessionInterface::class),

    SessionInterface::class => fn (Config $config) 
        => new PhpSession($config->get('session')),

    Twig::class => function (ContainerInterface $container) {
        $twig = Twig::create(
            $container->get(Config::class)->get('paths.views_dir'),
            $container->get(Config::class)->get('views.twig')
        );

        $twig->addExtension(new ViteExtension(
            $container->get(Config::class)->get('app.url'),
            $container->get(Config::class)
                ->get('paths.public_dir') . '/build/manifest.json',
            $container->get(Config::class)->get('app.vite_server')
        ));

        $twig->addExtension(new \Twig\Extension\DebugExtension());

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

    UserPasswordHasherInterface::class => fn (ContainerInterface $container)
        => $container->get(UserPasswordHasher::class),

    UserPasswordHasher::class => fn ()
        => new UserPasswordHasher(new PasswordHasherFactory(
            [
                User::class => new NativePasswordHasher(),
            ]
        )),

    UserAuthenticationService::class => fn (ContainerInterface $container)
        => new UserAuthenticationService(
            $container->get(EntityManager::class),
            $container->get(UserPasswordHasherInterface::class),
            $container->get(TokenStorageInterface::class),
            $container->get(AuthenticationTrustResolverInterface::class),
        ),

    TokenStorageInterface::class => fn () => new TokenStorage(),

    AuthenticationTrustResolverInterface::class => fn ()
        => new AuthenticationTrustResolver(),

    RequireAuthentication::class => fn (ContainerInterface $container)
        => new RequireAuthentication(
            $container->get(TokenStorageInterface::class),
            $container->get(App::class)->getResponseFactory()
        ),

    'view' => get(Twig::class),
    'config' => get(Config::class),
];
