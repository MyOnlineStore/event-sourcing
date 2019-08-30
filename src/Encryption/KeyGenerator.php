<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

interface KeyGenerator
{
    public function generate(): string;
}
