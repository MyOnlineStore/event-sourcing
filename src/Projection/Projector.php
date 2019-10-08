<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Projection;

use MyOnlineStore\EventSourcing\Event\Event;

abstract class Projector
{
    /** @var ReadModelRepository */
    protected $repository;

    public function __construct(ReadModelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Event $event): void
    {
        $parts = \explode('\\', \get_class($event));

        $this->{'apply'.\end($parts)}($event);
    }
}
