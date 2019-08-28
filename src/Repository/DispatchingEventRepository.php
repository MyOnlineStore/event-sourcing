<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DispatchingEventRepository extends EventRepositoryDecorator
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, EventRepository $innerRepository)
    {
        $this->dispatcher = $dispatcher;

        parent::__construct($innerRepository);
    }

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        parent::appendTo($streamName, $aggregateRootId, $eventStream);

        foreach ($eventStream as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
