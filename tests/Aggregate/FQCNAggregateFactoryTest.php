<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\FQCNAggregateFactory;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseAggregateRoot;
use PHPUnit\Framework\TestCase;

final class FQCNAggregateFactoryTest extends TestCase
{
    private FQCNAggregateFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new FQCNAggregateFactory();
    }

    public function testReconstituteFromHistory(): void
    {
        $aggregateRoot = BaseAggregateRoot::createForTest(
            $aggregateRootId = $this->createMock(AggregateRootId::class),
        );

        self::assertEquals(
            $aggregateRoot,
            $this->factory->reconstituteFromHistory(
                BaseAggregateRoot::class,
                $aggregateRootId,
                new Stream([], new StreamMetadata([])),
            ),
        );
    }
}
