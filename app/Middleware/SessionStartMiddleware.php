<?php

declare(strict_types = 1);

namespace Denosys\App\Middleware;

use Denosys\Core\Config\ConfigurationInterface;
use Denosys\Core\Encryption\Encrypter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionStartMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionManagerInterface $session,
        private Encrypter $encrypter,
        private ConfigurationInterface $config
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($_COOKIE[$this->config->get('session')['name']])) {
            $encryptedSessionId = $_COOKIE[$this->config->get('session')['name']];

            // $decryptedSessionId = $this->encrypter->decrypt($encryptedSessionId);
            // session_id($decryptedSessionId);
            
            try {
                $decryptedSessionId = $this->encrypter->decrypt($encryptedSessionId);
                session_id($decryptedSessionId);
            } catch (\InvalidArgumentException $e) {
                // Decryption failed; clear the session ID so that a new session will be started
                session_id('');
            }
        }

        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $response = $handler->handle($request);
        $this->session->save();

        return $response;
    }
}
