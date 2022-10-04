<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use PHPUnit\Framework\TestCase;

final class SnapshotTest extends TestCase
{
    public function testGetters(): void
    {
        $snapshot = new Snapshot(
            $aggregateId = $this->createMock(AggregateRootId::class),
            10,
            'foobar',
        );

        self::assertSame($aggregateId, $snapshot->getAggregateRootId());
        self::assertSame(10, $snapshot->getAggregateVersion());
        self::assertSame('foobar', $snapshot->getState());
    }
}
