<?php

namespace Deondazy\Core\Database\Entities;

use Deondazy\Core\Entity\Traits\HasTimestamps;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[Entity, Table(name: 'users')]
#[HasLifecycleCallbacks]
class User
{
    use HasTimestamps;
    
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(name: 'first_name')]
    private string $firstName;

    #[Column(name: 'last_name')]
    private string $lastName;

    #[Column(unique: true)]
    private string $email;

    #[Column(type: 'string')]
    private string $password;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return strtolower($this->email);
    }

    public function setEmail(string $email): User
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }
}
