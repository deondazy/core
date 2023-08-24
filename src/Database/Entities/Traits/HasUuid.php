<?php

declare(strict_types=1);

namespace Denosys\Core\Database\Entities\Traits;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping\PrePersist;

trait HasUuid
{
    #[PrePersist]
    public function generateUuid(): void
    {
        if (empty($this->getUuid())) {
            $uuid = Uuid::uuid4();
            $this->setUuid($uuid->toString());
        }
    }

    abstract public function getUuid(): ?string;
    abstract public function setUuid(string $uuid): void;
}
