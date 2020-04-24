<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

final class EncodingFailed extends EventSourcingException
{
    public static function fromPrevious(\Throwable $previous): self
    {
        return new self($previous->getMessage(), (int) $previous->getCode(), $previous);
    }
}
