<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Projection;

use MyOnlineStore\EventSourcing\Event\Event;

abstract class Projector
{
    public function __invoke(Event $event): void
    {
        $parts = \explode('\\', $event::class);

        $this->{'apply' . \end($parts)}($event);
    }
}
