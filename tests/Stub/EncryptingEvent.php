<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Stub;

use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\FieldEncrypting;

abstract class EncryptingEvent implements Event, FieldEncrypting
{
    /** @inheritDoc */
    public static function getEncryptingFields(): array
    {
        return ['foo'];
    }
}
