<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface FieldEncrypting
{
    /** @return list<string> */
    public static function getEncryptingFields(): array;
}
