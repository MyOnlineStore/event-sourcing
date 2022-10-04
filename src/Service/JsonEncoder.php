<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;

final class JsonEncoder implements Encoder
{
    /** @throws EncodingFailed */
    public function encode(mixed $value): string
    {
        try {
            return \json_encode(
                $value,
                \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION,
            );
        } catch (\JsonException $exception) {
            throw EncodingFailed::fromPrevious($exception);
        }
    }

    /** @throws EncodingFailed */
    public function decode(string $value): mixed
    {
        try {
            return \json_decode($value, true, 512, \JSON_THROW_ON_ERROR | \JSON_BIGINT_AS_STRING);
        } catch (\JsonException $exception) {
            throw EncodingFailed::fromPrevious($exception);
        }
    }
}
