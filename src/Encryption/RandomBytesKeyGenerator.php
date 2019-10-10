<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

final class RandomBytesKeyGenerator implements KeyGenerator
{
    /** @var int */
    private $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    /**
     * @throws \Exception
     */
    public function generate(): string
    {
        return \substr(
            \preg_replace(
                '/[^A-Za-z0-9]/',
                '',
                \base64_encode(
                    \random_bytes($this->length * 2)
                )
            ),
            0,
            $this->length
        );
    }
}
