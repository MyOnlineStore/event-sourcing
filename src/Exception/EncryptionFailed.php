<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

final class EncryptionFailed extends EventSourcingException
{
    public static function toDecrypt(?\Throwable $previous = null): self
    {
        return new self('Failed to decrypt message', 0, $previous);
    }

    public static function toEncrypt(?\Throwable $previous = null): self
    {
        return new self('Failed to encrypt message', 0, $previous);
    }
}
