<?php

declare(strict_types=1);

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
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;
use Deondazy\App\Middleware\RequireAuthentication;
use Odan\Session\Middleware\SessionStartMiddleware;
use Deondazy\App\Services\UserAuthenticationService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;

return [
    ServerRequestInterface::class => function () {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        return $serverRequestCreator->createServerRequestFromGlobals();
    },

    ResponseInterface::class => function () {
        return new Response();
    },

    'AppFactory' => function (ContainerInterface $container) {
        $factory = Bridge::create($container);
        
        $factory->addRoutingMiddleware();
        
        $factory->add(SessionStartMiddleware::class);
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

    SessionManagerInterface::class => function (ContainerInterface $container) {
        return $container->get(SessionInterface::class);
    },

    SessionInterface::class => function (Config $config) {
        $options = $config->get('session');

        return new PhpSession($options);
    },

    Twig::class => function (ContainerInterface $container) {

        $twig = Twig::create(
            $container->get(Config::class)->get('paths.views_dir'),
            $container->get(Config::class)->get('views.twig')
        );

        $twig->addExtension(new ViteExtension(
            $container->get(Config::class)->get('app.url'),
            $container->get(Config::class)->get('paths.public_dir') . '/build/manifest.json',
            $container->get(Config::class)->get('app.vite_server')
        ));

        $flash = $container->get(SessionInterface::class)->getFlash();
        $twig->getEnvironment()->addGlobal('flash', $flash);

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

    UserPasswordHasherInterface::class => function (ContainerInterface $container) {
        return $container->get(UserPasswordHasher::class);
    },

    UserPasswordHasher::class => function () {
        return new UserPasswordHasher(new PasswordHasherFactory([
            User::class => new NativePasswordHasher(),
        ]));
    },

    UserAuthenticationService::class => function (ContainerInterface $container) {
        return new UserAuthenticationService(
            $container->get(EntityManager::class),
            $container->get(UserPasswordHasherInterface::class),
            $container->get(TokenStorageInterface::class),
            $container->get(AuthenticationTrustResolverInterface::class),
        );
    },

    AuthenticationTrustResolverInterface::class => function () {
        return new AuthenticationTrustResolver();
    },

    AuthorizationCheckerInterface::class => function (ContainerInterface $container) {
        return $container->get(AuthorizationChecker::class);
    },

    TokenStorageInterface::class => DI\create(TokenStorage::class),
    TokenInterface::class => DI\get(TokenStorageInterface::class),

    AccessDecisionManagerInterface::class => function (ContainerInterface $container) {
        return new AccessDecisionManager([
            new AuthenticatedVoter($container->get(AuthenticationTrustResolverInterface::class)),
        ]);
    },

    AuthenticationTrustResolverInterface::class => function () {
        return new AuthenticationTrustResolver();
    },

    RequireAuthentication::class => function (TokenStorageInterface $tokenStorage) {
        return new RequireAuthentication($tokenStorage);
    },

    AuthorizationChecker::class => function (
        TokenStorageInterface $tokenStorage,
        AccessDecisionManagerInterface $accessDecisionManager
    ) {
        return new AuthorizationChecker($tokenStorage, $accessDecisionManager);
    },

    'view' => get(Twig::class),
    'config' => get(Config::class),
];
