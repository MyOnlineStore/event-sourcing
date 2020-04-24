<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\FQCNSnapshottingAggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseSnapshottingAggregateRoot;
use PHPUnit\Framework\TestCase;

final class FQCNSnapshottingAggregateFactoryTest extends TestCase
{
    private AggregateRootId $aggregateRootId;
    private FQCNSnapshottingAggregateFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FQCNSnapshottingAggregateFactory();
        $this->aggregateRootId = $this->createMock(AggregateRootId::class);
    }

    public function testReconstituteFromSnapshotAndHistory(): void
    {
        $aggregateRoot = BaseSnapshottingAggregateRoot::createForTest($this->aggregateRootId);
        $aggregateRoot->baseAction();

        self::assertEquals(
            $aggregateRoot,
            $this->factory->reconstituteFromSnapshotAndHistory(
                BaseSnapshottingAggregateRoot::class,
                $aggregateRoot->snapshot(),
                new Stream([], new StreamMetadata([]))
            )
        );
    }

    public function testReconstituteFromHistoryWithInvalidClass(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->factory->reconstituteFromSnapshotAndHistory(
            'foobar',
            new Snapshot($this->aggregateRootId, 1, ''),
            new Stream([], new StreamMetadata([]))
        );
    }

    public function testReconstituteFromHistoryWithNonAggregateRoot(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->factory->reconstituteFromSnapshotAndHistory(
            \stdClass::class,
            new Snapshot($this->aggregateRootId, 1, ''),
            new Stream([], new StreamMetadata([]))
        );
    }
}
