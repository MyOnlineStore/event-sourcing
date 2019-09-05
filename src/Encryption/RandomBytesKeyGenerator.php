<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

final class RandomBytesKeyGenerator implements KeyGenerator
{
    /** @var int */
    private $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    /**
     * @throws \Exception
     */
    public function generate(): string
    {
        return \bin2hex(\random_bytes($this->size));
    }
}
