<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;

interface Encoder
{
    /**
     * @throws EncodingFailed
     */
    public function encode(mixed $value): string;

    /**
     * @throws EncodingFailed
     */
    public function decode(string $value): mixed;
}
