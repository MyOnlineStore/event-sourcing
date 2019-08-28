<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

/**
 * @final
 */
class KeyGenerator
{
    /** @var int */
    private $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    public function generate(): string
    {
        return \bin2hex(\random_bytes($this->size));
    }
}
