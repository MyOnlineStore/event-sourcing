<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;

final class DefuseCryptoEncrypter implements Encrypter
{
    /**
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function decrypt(string $key, string $value): string
    {
        return Crypto::decrypt($value, Key::loadFromAsciiSafeString($key));
    }

    /**
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     */
    public function encrypt(string $key, string $value): string
    {
        return Crypto::encrypt($value, Key::loadFromAsciiSafeString($key));
    }
}
