<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseSnapshottingAggregateRoot;
use PHPUnit\Framework\TestCase;

final class SnapshottingAggregateRootTest extends TestCase
{
    private AggregateRootId $aggregateRootId;
    private BaseSnapshottingAggregateRoot $aggregateRoot;

    protected function setUp(): void
    {
        $this->aggregateRoot = BaseSnapshottingAggregateRoot::createForTest(
            $this->aggregateRootId = $this->createMock(AggregateRootId::class)
        );
    }

    public function testSnapshot(): void
    {
        $this->aggregateRoot->baseAction();

        self::assertEquals(
            new Snapshot(
                $this->aggregateRootId,
                1,
                \base64_encode(\serialize($this->aggregateRoot))
            ),
            $this->aggregateRoot->snapshot()
        );
    }

    public function testReconstituteFromSnapshot(): void
    {
        $this->aggregateRoot->baseAction();

        $aggregateRoot = BaseSnapshottingAggregateRoot::reconstituteFromSnapshotAndHistory(
            $this->aggregateRoot->snapshot(),
            new Stream([], new StreamMetadata([]))
        );

        self::assertInstanceOf(BaseSnapshottingAggregateRoot::class, $aggregateRoot);
        self::assertSame(1, $aggregateRoot->getVersion());
        self::assertSame('bar', $aggregateRoot->foo);
    }

    public function testReconstituteFromSnapshotAndHistory(): void
    {
        $this->aggregateRoot->baseAction();

        $aggregateRoot = BaseSnapshottingAggregateRoot::reconstituteFromSnapshotAndHistory(
            $this->aggregateRoot->snapshot(),
            new Stream(
                [BaseEvent::occur($this->aggregateRootId, ['foo' => 'qux'])->withVersion(2)],
                new StreamMetadata([])
            )
        );

        self::assertInstanceOf(BaseSnapshottingAggregateRoot::class, $aggregateRoot);
        self::assertSame(2, $aggregateRoot->getVersion());
        self::assertSame('qux', $aggregateRoot->foo);
    }
}
