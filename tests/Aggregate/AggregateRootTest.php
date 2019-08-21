<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    /** @var AggregateRoot */
    private $aggregateRoot;

    /** @var AggregateRootId */
    private $aggregateRootId;

    protected function setUp(): void
    {
        $this->aggregateRootId = $this->createMock(AggregateRootId::class);

        // phpcs:disable
        $this->aggregateRoot = new class($this->aggregateRootId) extends AggregateRoot
        {
            public $foo;

            public function __construct(AggregateRootId $aggregateRootId)
            {
                parent::__construct($aggregateRootId);
            }

            public function baseAction(): void
            {
                $this->recordThat(BaseEvent::occur(['foo' => 'bar']));
            }

            protected function applyBaseEvent(BaseEvent $event): void
            {
                $this->foo = $event->getPayload()['foo'];
            }
        };
        // phpcs:enable
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
    }

    public function testReconstituteFromHistory(): void
    {
        /** @var AggregateRoot $aggregateName */
        $aggregateName = \get_class($this->aggregateRoot);
        $aggregateRoot = $aggregateName::reconstituteFromHistory(
            $this->aggregateRootId,
            [BaseEvent::occur(['foo' => 'qux'])]
        );

        self::assertSame(1, $aggregateRoot->getVersion());
        self::assertSame('qux', $aggregateRoot->foo);
        self::assertEmpty($aggregateRoot->popRecordedEvents());
    }
}
