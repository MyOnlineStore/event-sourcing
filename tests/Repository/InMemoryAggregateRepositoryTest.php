<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\FQCNAggregateFactory;
use MyOnlineStore\EventSourcing\Repository\InMemoryAggregateRepository;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseAggregateRoot;
use PHPUnit\Framework\TestCase;

final class InMemoryAggregateRepositoryTest extends TestCase
{
    private InMemoryAggregateRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAggregateRepository(
            new FQCNAggregateFactory(),
            BaseAggregateRoot::class
        );
    }

    public function testInMemoryRepository(): void
    {
        $aggregateA = BaseAggregateRoot::createForTest(
            $aggregateIdA = AggregateRootId::generate()
        );

        $this->repository->save($aggregateA);

        self::assertSame($aggregateA, $this->repository->load($aggregateIdA));

        $aggregateIdB = AggregateRootId::generate();
        $aggregateB = $this->repository->load($aggregateIdB);

        self::assertInstanceOf(BaseAggregateRoot::class, $aggregateB);
        self::assertNotSame($aggregateB, $aggregateA);
    }
}
