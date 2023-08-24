<?php

declare(strict_types=1);

namespace Denosys\Core\Commands;

use Symfony\Component\Console\Command\Command;
use Denosys\Core\Config\ConfigurationInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEncryptionKeyCommand extends Command
{
    protected static $defaultName = 'key:generate';

    public function __construct(private readonly ConfigurationInterface $config, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Generate an encryption key and update the .env file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $key = 'base64:' . base64_encode(openssl_random_pseudo_bytes(32));
        $key = base64_encode(sodium_crypto_secretbox_keygen());

        $envFile = $this->config->get('paths.root_dir') . '/.env';
        $contents = file_get_contents($envFile);
        $contents = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . $key, $contents);
        file_put_contents($envFile, $contents);

        $output->writeln('<info>Encryption key generated and saved to .env file.</info>');

        return Command::SUCCESS;
    }
}
