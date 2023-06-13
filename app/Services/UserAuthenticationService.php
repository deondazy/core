<?php

declare(strict_types = 1);

namespace Deondazy\App\Services;

use Deondazy\App\Database\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;

class UserAuthenticationService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected UserPasswordHasherInterface $passwordHasher,
        protected TokenStorageInterface $tokenStorage,
        protected AuthenticationTrustResolverInterface $authenticationChecker
    ) {}
    
    public function register(array $formData): void
    {
        $user = new User();
        $user->setFirstName($formData['first_name'])
        ->setLastName($formData['last_name'])
        ->setEmail($formData['email']);
        
        $hashedPassword = $this->passwordHasher
            ->hashPassword($user, $formData['password']);
        $user->setPassword($hashedPassword)
            ->setRoles(['ROLE_USER']);
        

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function login(array $credentials): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $credentials['email']
        ]);

        if (!$user instanceof UserInterface) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = new UsernamePasswordToken($user,'main', $user->getRoles());
        
        if (!$this->authenticationChecker->isAuthenticated($token)) {
            throw new AuthenticationException('Access Denied.');
        } 
        
        $this->tokenStorage->setToken($token);
    }
}
