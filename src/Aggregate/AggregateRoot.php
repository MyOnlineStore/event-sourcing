<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;

abstract class AggregateRoot
{
    /** @var AggregateRootId */
    protected $aggregateRootId;

    /** @var Event[] */
    protected $recordedEvents = [];

    /** @var int */
    protected $version = 0;

    protected function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }

    public function getAggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Event[]
     */
    public function popRecordedEvents(): array
    {
        $pending = $this->recordedEvents;
        $this->recordedEvents = [];

        return $pending;
    }

    public static function reconstituteFromHistory(AggregateRootId $aggregateRootId, Stream $eventStream): AggregateRoot
    {
        $instance = new static($aggregateRootId);

        foreach ($eventStream as $event) {
            $instance->apply($event);
        }

        return $instance;
    }

    protected function apply(Event $event): void
    {
        $this->version = $event->getVersion();
        $parts = \explode('\\', \get_class($event));

        $this->{'apply'.\end($parts)}($event);
    }

    protected function recordThat(Event $event): void
    {
        $event = $event->withVersion($this->version + 1);

        $this->apply($event);
        $this->recordedEvents[] = $event;
    }
}
