<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\FQCNSnapshottingAggregateFactory;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseSnapshottingAggregateRoot;
use PHPUnit\Framework\TestCase;

final class FQCNSnapshottingAggregateFactoryTest extends TestCase
{
    private FQCNSnapshottingAggregateFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FQCNSnapshottingAggregateFactory();
    }

    public function testReconstituteFromSnapshotAndHistory(): void
    {
        $aggregateRoot = BaseSnapshottingAggregateRoot::createForTest(AggregateRootId::generate());
        $aggregateRoot->baseAction();

        self::assertEquals(
            $aggregateRoot,
            $this->factory->reconstituteFromSnapshotAndHistory(
                BaseSnapshottingAggregateRoot::class,
                $aggregateRoot->snapshot(),
                new Stream([], new StreamMetadata([])),
            ),
        );
    }
}
