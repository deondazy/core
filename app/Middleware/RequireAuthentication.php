<?php

declare(strict_types=1);

namespace Deondazy\App\Middleware;

use Deondazy\App\Services\TokenStorageService;
use InvalidArgumentException;
use Slim\Psr7\Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class RequireAuthentication implements MiddlewareInterface
{
    public function __construct(
        private TokenStorageService $tokenStorage,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            $token = $this->tokenStorage->getToken($request);

            if ($token === null) {
                throw new AuthenticationCredentialsNotFoundException('Authentication credentials could not be found.');
            }

            $user = $token->getUser();

            if (!$user instanceof UserInterface) {
                throw new AccessDeniedException('Access Denied.');
            }

            return $handler->handle($request);
        } catch (AccessDeniedException $e) {
            $response = $this->responseFactory->createResponse();

            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $response = $this->responseFactory->createResponse();
            
            return $response->withHeader('Location', '/login')->withStatus(302);
        } catch (InvalidArgumentException $e) {
            $response = $this->responseFactory->createResponse();
            
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    }
}
