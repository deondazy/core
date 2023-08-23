<?php

declare(strict_types=1);

namespace Denosys\App\Services;

use Denosys\Core\Session\SessionInterface;
use Denosys\Core\Encryption\Encrypter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenStorageService
{

    public function __construct(
        private Encrypter $encrypter,
        private SessionInterface $session,
        private TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager,
        private readonly string $cookieName = 'session'
    ) {
    }

    public function setToken(UsernamePasswordToken $token): void
    {
        $this->session->regenerateId();

        $this->tokenStorage->setToken($token);

        $serializedToken = serialize($token);
        $encryptedToken = $this->encrypter->encrypt($serializedToken);

        $this->session->set($this->cookieName, $encryptedToken);
    }

    public function getToken(): ?TokenInterface
    {
        $encryptedToken = $this->session->get($this->cookieName) ?? null;

        if (!$encryptedToken) {
            return null;
        }

        $serializedToken = $this->encrypter->decrypt($encryptedToken);

        return unserialize($serializedToken);
    }

    public function logout(): void
    {
        $this->tokenStorage->setToken(null);
        $this->session->clear();
    }
}
