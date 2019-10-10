<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Projection;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\ReadModelNotFound;

interface ReadModelRepository
{
    public function delete(AggregateRootId $id): void;

    /**
     * @throws ReadModelNotFound
     */
    public function load(AggregateRootId $id): ReadModel;

    public function save(ReadModel $model): void;
}
