<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;

interface Encrypter
{
    /** @throws EncryptionFailed */
    public function decrypt(string $key, string $value): string;

    /** @throws EncryptionFailed */
    public function encrypt(string $key, string $value): string;
}
