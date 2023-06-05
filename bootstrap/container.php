<?php 

declare(strict_types = 1);

use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

return [
    ServerRequestInterface::class => function () {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        return $serverRequestCreator->createServerRequestFromGlobals();
    },
];