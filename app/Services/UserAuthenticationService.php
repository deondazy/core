<?php

declare(strict_types = 1);

namespace Deondazy\App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Deondazy\App\Database\Entities\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAuthenticationService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected UserPasswordHasherInterface $passwordHasher
    ) {}
    
    public function register(array $formData): void
    {
        $user = new User();
        $user->setFirstName($formData['first_name']);
        $user->setLastName($formData['last_name']);
        $user->setEmail($formData['email']);

        $hashedPassword = $this->passwordHasher
            ->hashPassword($user, $formData['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
