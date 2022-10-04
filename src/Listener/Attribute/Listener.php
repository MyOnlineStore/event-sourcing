<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Listener\Attribute;

/** @psalm-immutable */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Listener
{
    public function __construct(
        public string $event,
    ) {
    }
}
