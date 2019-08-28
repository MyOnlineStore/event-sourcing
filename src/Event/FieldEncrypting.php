<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface FieldEncrypting
{
    public static function getEncryptingFields(): EncryptingFields;
}
