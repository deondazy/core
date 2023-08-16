<?php

declare(strict_types=1);

use Slim\App;
use function Di\get;
use Slim\Views\Twig;
use Slim\Psr7\Response;
use Valitron\Validator;
use DI\Bridge\Slim\Bridge;
use Doctrine\ORM\ORMSetup;
use Odan\Session\PhpSession;
use Slim\Views\TwigMiddleware;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Odan\Session\SessionInterface;
use Deondazy\Core\View\ViteExtension;
use Psr\Container\ContainerInterface;
use Deondazy\Core\Encryption\Encrypter;
use Psr\Http\Message\ResponseInterface;
use Deondazy\App\Database\Entities\User;
use Dotenv\Repository\RepositoryBuilder;
use Odan\Session\SessionManagerInterface;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Deondazy\App\Services\TokenStorageService;
use Deondazy\Core\Config\ConfigurationManager;
use Deondazy\Core\Config\ArrayFileConfiguration;
use Deondazy\Core\Config\ConfigurationInterface;
use Deondazy\Core\Environment\EnvironmentLoader;
use Deondazy\App\Middleware\RequireAuthentication;
use Deondazy\App\Middleware\SessionStartMiddleware;
use Deondazy\App\Services\UserAuthenticationService;
use Deondazy\App\Middleware\SessionEncryptMiddleware;
use Deondazy\Core\Environment\EnvironmentLoaderInterface;
use Deondazy\App\Middleware\ValidationExceptionMiddleware;
use Deondazy\App\Middleware\FlashValidationErrorMiddleware;
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
        
        $app->add(TwigMiddleware::createFromContainer($app));
        $app->add(SessionStartMiddleware::class);
        $app->add(SessionEncryptMiddleware::class);
        $app->add(ValidationExceptionMiddleware::class);
        $app->add(FlashValidationErrorMiddleware::class);
        $app->add(new WhoopsMiddleware());

        return $app;
    },

    EnvironmentLoaderInterface::class => function () {
        $builder = RepositoryBuilder::createWithDefaultAdapters();
        $builder = $builder->addAdapter(PutenvAdapter::class);
        $repository = $builder->immutable()->make();

        return new EnvironmentLoader($repository);
    },

    ResponseInterface::class => fn () => new Response(),

    EntityManager::class => function (ConfigurationInterface $config) {
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

    SessionInterface::class => fn (ConfigurationInterface $config) 
        => new PhpSession($config->get('session')),

    Twig::class => function (ConfigurationInterface $config) {
        $twig = Twig::create(
            $config->get('paths.views_dir'),
            $config->get('views.twig')
        );

        $twig->addExtension(new ViteExtension(
            $config->get('app.url') . '/build',
            $config
                ->get('paths.build_dir') . '/manifest.json',
            $config->get('app.vite_server')
        ));

        $twig->addExtension(new \Twig\Extension\DebugExtension());

        return $twig;
    },

    ConfigurationInterface::class => function (ContainerInterface $container) {
        $directory = __DIR__ . '/../config/';

        $configurationManager = new ConfigurationManager(
            $container->get(EnvironmentLoaderInterface::class)
        );

        $config = $configurationManager->loadConfigurationFiles($directory);

        return new ArrayFileConfiguration(
            $container->get(EnvironmentLoaderInterface::class),
            $config
        );
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
            $container->get(TokenStorageService::class),
            $container->get(AuthenticationTrustResolverInterface::class)
        ),
    
    TokenStorageService::class => fn (ContainerInterface $container)
        => new TokenStorageService(
            $container->get(Encrypter::class),
            $container->get(SessionInterface::class),
            new TokenStorage(),
            $container->get(EntityManager::class),
            $container->get('config')->get('session.name')
        ),

    Validator::class => fn () => new Validator(),

    TokenStorageInterface::class => fn() => new TokenStorage(),

    Encrypter::class => fn(ConfigurationInterface $config) 
        => new Encrypter($config->get('app.key')),

    AuthenticationTrustResolverInterface::class => fn ()
        => new AuthenticationTrustResolver(),

    RequireAuthentication::class => fn (ContainerInterface $container)
        => new RequireAuthentication(
            $container->get(TokenStorageService::class),
            $container->get(App::class)->getResponseFactory()
        ),

    ValidationExceptionMiddleware::class => function (ContainerInterface $container) {
        return new ValidationExceptionMiddleware(
            $container->get(App::class)->getResponseFactory(),
            $container->get(SessionInterface::class)
        );
    },

    'view' => get(Twig::class),
    'config' => get(ConfigurationInterface::class),
];
