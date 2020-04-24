<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Aggregate\SnapshottingAggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\SnapshottingAggregateRoot;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;
use MyOnlineStore\EventSourcing\Repository\AggregateRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Repository\MetadataRepository;
use MyOnlineStore\EventSourcing\Repository\SnapshotRepository;
use MyOnlineStore\EventSourcing\Repository\SnapshottingAggregateRepository;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseAggregateRoot;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseSnapshottingAggregateRoot;
use PHPUnit\Framework\TestCase;

final class SnapshottingAggregateRepositoryTest extends TestCase
{
    private BaseSnapshottingAggregateRoot $aggregateRoot;
    private AggregateRootId $aggregateRootId;
    private AggregateRepository $innerRepository;
    private EventRepository $eventRepository;
    private MetadataRepository $metadataRepository;
    private SnapshottingAggregateRepository $repository;
    private SnapshotRepository $snapshotRepository;
    private SnapshottingAggregateFactory $aggregateFactory;
    /** @psalm-var class-string<SnapshottingAggregateRoot> $aggregateName */
    private string $aggregateName;
    private string $streamName;

    protected function setUp(): void
    {
        $this->repository = new SnapshottingAggregateRepository(
            $this->innerRepository = $this->createMock(AggregateRepository::class),
            $this->eventRepository = $this->createMock(EventRepository::class),
            $this->metadataRepository = $this->createMock(MetadataRepository::class),
            $this->snapshotRepository = $this->createMock(SnapshotRepository::class),
            $this->aggregateFactory = $this->createMock(SnapshottingAggregateFactory::class),
            $this->aggregateName = 'aggregate_name',
            $this->streamName = 'stream_name'
        );

        $this->aggregateRoot = BaseSnapshottingAggregateRoot::createForTest(
            $this->aggregateRootId = $this->createMock(AggregateRootId::class)
        );
    }

    public function testLoad(): void
    {
        $this->snapshotRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $this->aggregateRootId)
            ->willReturn($snapshot = new Snapshot($this->aggregateRootId, 12, 'aggregate_state'));

        $this->metadataRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $this->aggregateRootId)
            ->willReturn($metadata = new StreamMetadata([]));

        $this->eventRepository->expects(self::once())
            ->method('loadAfterVersion')
            ->with(
                $this->streamName,
                $this->aggregateRootId,
                12,
                $metadata
            )
            ->willReturn($stream = new Stream([], $metadata));

        $this->aggregateFactory->expects(self::once())
            ->method('reconstituteFromSnapshotAndHistory')
            ->with(
                $this->aggregateName,
                $snapshot,
                $stream
            )
            ->willReturn($this->aggregateRoot);

        self::assertSame($this->aggregateRoot, $this->repository->load($this->aggregateRootId));
    }

    public function testLoadUsesInnerRepositoryIfNoSnapshotFound(): void
    {
        $this->snapshotRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $this->aggregateRootId)
            ->willThrowException(SnapshotNotFound::withAggregateRootId($this->aggregateRootId));

        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($this->aggregateRootId)
            ->willReturn($this->aggregateRoot);

        self::assertSame($this->aggregateRoot, $this->repository->load($this->aggregateRootId));
    }

    public function testSave(): void
    {
        $this->innerRepository->expects(self::once())
            ->method('save')
            ->with($this->aggregateRoot);

        $this->snapshotRepository->expects(self::once())
            ->method('save')
            ->with($this->streamName, $this->aggregateRoot->snapshot());

        $this->repository->save($this->aggregateRoot);
    }

    public function testSaveDoesNotSaveSnapshotIfNotSnapshottingAggregate(): void
    {
        $aggregateRoot = BaseAggregateRoot::createForTest($this->aggregateRootId);

        $this->innerRepository->expects(self::once())
            ->method('save')
            ->with($aggregateRoot);

        $this->snapshotRepository->expects(self::never())->method('save');

        $this->repository->save($aggregateRoot);
    }
}
