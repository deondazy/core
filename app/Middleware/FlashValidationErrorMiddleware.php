<?php

declare(strict_types=1);

namespace Denosys\App\Middleware;

use Slim\Views\Twig;
use Denosys\Core\Session\SessionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class FlashValidationErrorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Twig $twig,
        private SessionInterface $session
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($this->session->has('errors')) {
            $errors = $this->session->get('errors');

            $this->twig->getEnvironment()->addGlobal('errors', $errors);

            $this->session->delete('errors');
        }

        return $handler->handle($request);
    }
}
