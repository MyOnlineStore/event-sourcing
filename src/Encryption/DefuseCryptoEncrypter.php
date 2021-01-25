<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;

final class DefuseCryptoEncrypter implements Encrypter
{
    public function decrypt(string $key, string $value): string
    {
        try {
            return Crypto::decrypt($value, Key::loadFromAsciiSafeString($key));
        } catch (BadFormatException | EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException $exception) {
        }

        throw EncryptionFailed::toDecrypt($exception);
    }

    public function encrypt(string $key, string $value): string
    {
        try {
            return Crypto::encrypt($value, Key::loadFromAsciiSafeString($key));
        } catch (BadFormatException | EnvironmentIsBrokenException $exception) {
        }

        throw EncryptionFailed::toEncrypt($exception);
    }
}
