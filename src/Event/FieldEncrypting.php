<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface FieldEncrypting
{
    /**
     * @return string[]
     */
    public static function getEncryptingFields(): array;
}
