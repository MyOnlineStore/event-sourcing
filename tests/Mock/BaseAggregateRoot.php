<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Mock;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;

final class BaseAggregateRoot extends AggregateRoot
{
    public string $foo;

    public static function createForTest(AggregateRootId $aggregateRootId): self
    {
        return new self($aggregateRootId);
    }

    public function baseAction(): void
    {
        $this->recordThat(BaseEvent::occur($this->aggregateRootId, ['foo' => 'bar']));
    }

    protected function applyBaseEvent(BaseEvent $event): void
    {
        $this->foo = $event->getPayload()['foo'];
    }
}
