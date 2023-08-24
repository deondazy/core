<?php

declare(strict_types=1);

namespace Denosys\Core\Encryption;

class Encrypter
{

    public function __construct(private readonly string $key)
    {
        $key = $this->getKey();

        if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \InvalidArgumentException('Key must be ' . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes.');
        }
    }

    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $cipher = sodium_crypto_secretbox(
            $data,
            $nonce,
            $this->getKey()
        );

        return base64_encode($nonce . $cipher);
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload);

        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64 payload.');
        }

        if (mb_strlen($decoded, '8bit') < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new \InvalidArgumentException('Payload is too small.');
        }

        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipher = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain = sodium_crypto_secretbox_open(
            $cipher,
            $nonce,
            $this->getKey()
        );

        if ($plain === false) {
            throw new \InvalidArgumentException('Invalid payload or key.');
        }

        return $plain;
    }

    public function getKey(): string
    {
        return base64_decode($this->key);
    }
}
