<?php

declare(strict_types = 1);

use Denosys\Core\Commands\GenerateEncryptionKeyCommand;
use Denosys\Core\Config\ConfigurationInterface;

return fn(ConfigurationInterface $config) => [
    new GenerateEncryptionKeyCommand($config),
];