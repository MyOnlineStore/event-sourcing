<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Repository\AggregateRepository;
use MyOnlineStore\EventSourcing\Repository\RuntimeCachedAggregateRepository;
use PHPUnit\Framework\TestCase;

final class RuntimeCachedAggregateRepositoryTest extends TestCase
{
    /** @var AggregateRepository */
    private $innerRepository;

    /** @var RuntimeCachedAggregateRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new RuntimeCachedAggregateRepository(
            $this->innerRepository = $this->createMock(AggregateRepository::class)
        );
    }

    public function testLoadOnlyCallsInnerRepositoryOnFirstCall(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);

        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($aggregateRootId)
            ->willReturn($aggregateRoot = $this->createMock(AggregateRoot::class));

        self::assertSame($aggregateRoot, $this->repository->load($aggregateRootId));
        self::assertSame($aggregateRoot, $this->repository->load($aggregateRootId));
    }

    public function testSaveAlwaysCallsInnerRepositoryAndUpdatesRuntimeCache(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRoot = $this->createMock(AggregateRoot::class);
        $aggregateRoot->method('getAggregateRootId')->willReturn($aggregateRootId);
        $aggregateRoot2 = $this->createMock(AggregateRoot::class);
        $aggregateRoot2->method('getAggregateRootId')->willReturn($aggregateRootId);

        $this->innerRepository->expects(self::exactly(2))
            ->method('save')
            ->withConsecutive([$aggregateRoot], [$aggregateRoot2]);

        $this->innerRepository->expects(self::never())->method('load');

        $this->repository->save($aggregateRoot);
        self::assertSame($aggregateRoot, $this->repository->load($aggregateRootId));

        $this->repository->save($aggregateRoot2);
        self::assertSame($aggregateRoot2, $this->repository->load($aggregateRootId));
    }
}
