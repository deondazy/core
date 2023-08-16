<?php

declare(strict_types = 1);

use Deondazy\Core\Commands\GenerateEncryptionKeyCommand;
use Deondazy\Core\Config\ConfigurationInterface;

return fn(ConfigurationInterface $config) => [
    new GenerateEncryptionKeyCommand($config),
];