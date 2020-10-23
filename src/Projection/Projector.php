<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Projection;

use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Listener\AttributeListenerAware;

abstract class Projector
{
    use AttributeListenerAware;

    public function __invoke(Event $event): void
    {
        foreach ($this->getListeners($event::class) as $listener) {
            $listener($event);
        }
    }
}
