<?php

declare(strict_types=1);

namespace Denosys\Core;

use Denosys\Core\Environment\EnvironmentLoaderInterface;

class Application
{
    private static Application $instance;

    private function __construct(private readonly EnvironmentLoaderInterface $environmentLoader)
    {
    }

    public static function getInstance(): Application
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application(
                new \Denosys\Core\Environment\EnvironmentLoader(
                    \Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()->make()
                )
            );
        }

        return self::$instance;
    }

    public function get(string $abstract): mixed
    {
        switch ($abstract) {
            case EnvironmentLoaderInterface::class:
                return $this->environmentLoader;
        }

        throw new \InvalidArgumentException("Unknown abstract {$abstract}");
    }
}
