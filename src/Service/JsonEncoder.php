<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;

final class JsonEncoder implements Encoder
{
    /**
     * @param mixed $value
     *
     * @throws EncodingFailed
     */
    public function encode($value): string
    {
        $json = \json_encode($value, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION);

        if (\JSON_ERROR_NONE !== $error = \json_last_error()) {
            throw EncodingFailed::toEncode(\json_last_error_msg(), $error);
        }

        return $json;
    }

    /**
     * @return mixed
     *
     * @throws EncodingFailed
     */
    public function decode(string $json)
    {
        $data = \json_decode($json, true, 512, \JSON_BIGINT_AS_STRING);

        if (\JSON_ERROR_NONE !== $error = \json_last_error()) {
            throw EncodingFailed::toDecode(\json_last_error_msg(), $error);
        }

        return $data;
    }
}
