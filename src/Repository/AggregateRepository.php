<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

interface AggregateRepository
{
    public function load(AggregateRootId $aggregateRootId): AggregateRoot;

    public function save(AggregateRoot $aggregateRoot): void;
}
