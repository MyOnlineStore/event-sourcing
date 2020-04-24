<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseAggregateRoot;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    private BaseAggregateRoot $aggregateRoot;
    private AggregateRootId $aggregateRootId;

    protected function setUp(): void
    {
        $this->aggregateRoot = BaseAggregateRoot::createForTest(
            $this->aggregateRootId = $this->createMock(AggregateRootId::class)
        );
    }

    public function testInitialState(): void
    {
        self::assertSame(0, $this->aggregateRoot->getVersion());
        self::assertSame($this->aggregateRootId, $this->aggregateRoot->getAggregateRootId());
        self::assertEmpty($this->aggregateRoot->popRecordedEvents());
    }

    public function testApplyAction(): void
    {
        $this->aggregateRoot->baseAction();

        self::assertSame(1, $this->aggregateRoot->getVersion());
        self::assertSame('bar', $this->aggregateRoot->foo);
        self::assertNotEmpty($this->aggregateRoot->popRecordedEvents());
        self::assertEmpty($this->aggregateRoot->popRecordedEvents());
    }

    public function testReconstituteFromHistory(): void
    {
        $aggregateRoot = BaseAggregateRoot::reconstituteFromHistory(
            $this->aggregateRootId,
            new Stream(
                [BaseEvent::occur($this->aggregateRootId, ['foo' => 'qux'])],
                new StreamMetadata([])
            )
        );

        self::assertSame(1, $aggregateRoot->getVersion());
        self::assertSame('qux', $aggregateRoot->foo);
        self::assertEmpty($aggregateRoot->popRecordedEvents());
    }
}
