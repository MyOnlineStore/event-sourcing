<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;

interface Encoder
{
    /**
     * @param mixed $value
     *
     * @throws EncodingFailed
     */
    public function encode($value): string;

    /**
     * @return mixed
     *
     * @throws EncodingFailed
     */
    public function decode(string $value);
}
