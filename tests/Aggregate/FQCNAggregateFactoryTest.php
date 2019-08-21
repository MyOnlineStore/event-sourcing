<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\FQCNAggregateFactory;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use PHPUnit\Framework\TestCase;

final class FQCNAggregateFactoryTest extends TestCase
{
    /** @var FQCNAggregateFactory */
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new FQCNAggregateFactory();
    }

    public function testReconstituteFromHistory(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);

        // phpcs:disable
        $aggregateRoot = new class($aggregateRootId) extends AggregateRoot
        {
            public function __construct(AggregateRootId $aggregateRootId)
            {
                parent::__construct($aggregateRootId);
            }
        };
        // phpcs:enable

        self::assertEquals(
            $aggregateRoot,
            $this->factory->reconstituteFromHistory(\get_class($aggregateRoot), $aggregateRootId, [])
        );
    }

    public function testReconstituteFromHistoryWithInvalidClass(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->factory->reconstituteFromHistory('foobar', $this->createMock(AggregateRootId::class), []);
    }

    public function testReconstituteFromHistoryWithNonAggregateRoot(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->factory->reconstituteFromHistory(\stdClass::class, $this->createMock(AggregateRootId::class), []);
    }
}
