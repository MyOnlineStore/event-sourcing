<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

final class EncodingFailed extends EventSourcingException
{
    public static function toDecode(string $message, int $code): self
    {
        return new self($message, $code);
    }

    public static function toEncode(string $message, int $code): self
    {
        return new self($message, $code);
    }
}
