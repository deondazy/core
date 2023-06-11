<?php

declare(strict_types=1);

namespace Deondazy\App\Middleware;

use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class RequireAuthentication
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            $token = $this->tokenStorage->getToken();

            if ($token === null) {
                throw new AuthenticationCredentialsNotFoundException('Authentication credentials could not be found.');
            }

            $user = $token->getUser();

            if (!$user instanceof UserInterface) {
                throw new AccessDeniedException('Access Denied.');
            }

            // Continue processing the request
             return $handler->handle($request);
        } catch (AccessDeniedException $e) {
            $headers = new Headers(['Location' => '/login']);
            return new Response(302, $headers);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $headers = new Headers(['Location' => '/login']);
            return new Response(302, $headers);
        }
    }
}
