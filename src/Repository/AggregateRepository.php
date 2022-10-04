<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

/** @template T of AggregateRoot */
interface AggregateRepository
{
    /** @return T */
    public function load(AggregateRootId $aggregateRootId): AggregateRoot;

    /** @param T $aggregateRoot */
    public function save(AggregateRoot $aggregateRoot): void;
}
