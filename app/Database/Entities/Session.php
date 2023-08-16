<?php

namespace Deondazy\App\Database\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sessions')]
class Session
{   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    private User|null $user = null;
    
    #[ORM\Column(name: 'ip_address', type: Types::STRING)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'user_agent', type: Types::STRING)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'payload', type: Types::STRING)]
    private ?string $token = null;

    #[ORM\Column(name: 'last_activity', type: Types::INTEGER)]
    private ?string $lastActivity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setToken(string $token): Session
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setIpAddress(string $ipAddress): Session
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setUserAgent(string $userAgent): Session
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setLastActivity(string $lastActivity): Session
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getLastActivity(): ?string
    {
        return $this->lastActivity;
    }

    public function setUser(User $user): Session
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
