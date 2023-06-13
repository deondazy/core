<?php

declare(strict_types = 1);

namespace Deondazy\App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Deondazy\Core\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $e) {
            $response = $this->responseFactory->createResponse();
            
            return $response->withStatus(302)
                ->withHeader('Location', $request->getUri()->getPath());
        }
    }
}