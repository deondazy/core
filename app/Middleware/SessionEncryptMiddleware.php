<?php 

declare(strict_types = 1);

namespace Denosys\App\Middleware;

use Denosys\Core\Config\ConfigurationInterface;
use Denosys\Core\Encryption\Encrypter;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionEncryptMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Encrypter $encrypter,
        private ConfigurationInterface $config
    ) {
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        $sessionId = session_id();

        $encryptedSessionId = $this->encrypter->encrypt($sessionId);

        $sessionAttributes = $this->config->get('session');

        $cookieString = $sessionAttributes['name'] . "=$encryptedSessionId; ";
        $cookieString .= $sessionAttributes['path'] ? "Path=" . $sessionAttributes['path'] . "; " : "";
        $cookieString .= $sessionAttributes['domain'] ? "Domain=" . $sessionAttributes['domain'] . "; " : "";
        $cookieString .= $sessionAttributes['secure'] ? "Secure; " : "";
        $cookieString .= $sessionAttributes['httponly'] ? "HttpOnly; " : "";
        $cookieString .= $sessionAttributes['same_site'] ? "SameSite=" . $sessionAttributes['same_site'] . "; " : "";
        $cookieString .= "Max-Age=" . $sessionAttributes['lifetime'] . "; ";

        $response = $response->withAddedHeader('Set-Cookie', $cookieString);

        return $response;
    }
}