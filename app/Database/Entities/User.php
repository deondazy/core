<?php

namespace Deondazy\App\Database\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Deondazy\App\Database\Entities\Session;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Security\Core\User\UserInterface;
use Deondazy\App\Database\Entities\Traits\WithTimestamps;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use WithTimestamps;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'reference_id', type: Types::STRING)]
    private ?string $referenceId = null;

    #[ORM\Column(name: 'first_name', type: Types::STRING)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', type: Types::STRING)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $roles = [];

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'user')]
    private Collection $sessions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setReferenceId(string $referenceId): User
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    
    public function getEmail(): string
    {
        return $this->getUserIdentifier();
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function eraseCredentials(): void
    {
        // ...
    }

    public function __serialize(): array
    {
        return [$this->referenceId];
    }

    public function __unserialize(array $data): void
    {
        [$this->referenceId] = $data;
    }
}
