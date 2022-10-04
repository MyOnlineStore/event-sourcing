<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Mock;

use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Listener\Attribute\Listener;
use MyOnlineStore\EventSourcing\Projection\Projector;

final class BaseProjector extends Projector
{
    public function __construct(
        private \stdClass $model,
    ) {
    }

    #[Listener(BaseEvent::class)]
    protected function applyEvent(BaseEvent $event): void
    {
        $this->model->foo = $event->getPayload()['foo'];
    }
}
