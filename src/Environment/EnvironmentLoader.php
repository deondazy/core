<?php

declare(strict_types=1);

namespace Denosys\Core\Environment;

use PhpOption\Option;
use Denosys\Core\Environment\EnvironmentLoaderInterface;
use Dotenv\Repository\RepositoryInterface;

class EnvironmentLoader implements EnvironmentLoaderInterface
{
    public function __construct(private readonly RepositoryInterface $repository)
    {
    }

    public function get(string $key, $default = null)
    {
        return Option::fromValue($this->repository->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return null;
                }
                return $value;
            })
            ->getOrElse($default);
    }
}
