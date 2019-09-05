<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;
use Zend\Crypt\BlockCipher;

final class ZendBlockCipherEncrypter implements Encrypter
{
    /** @var BlockCipher */
    private $blockCipher;

    public function __construct(BlockCipher $blockCipher)
    {
        $this->blockCipher = $blockCipher;
    }

    /**
     * @throws EncryptionFailed
     */
    public function decrypt(string $key, string $value): string
    {
        try {
            $this->blockCipher->setKey($key);
            $decrypted = $this->blockCipher->decrypt($value);
        } catch (\Throwable $exception) {
            throw EncryptionFailed::toDecrypt($exception);
        }

        if (!\is_string($decrypted)) {
            throw EncryptionFailed::toDecrypt();
        }

        return $decrypted;
    }

    /**
     * @throws EncryptionFailed
     */
    public function encrypt(string $key, string $value): string
    {
        try {
            $this->blockCipher->setKey($key);

            return $this->blockCipher->encrypt($value);
        } catch (\Throwable $exception) {
            throw EncryptionFailed::toDecrypt($exception);
        }
    }
}
