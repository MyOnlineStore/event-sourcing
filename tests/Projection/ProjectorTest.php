<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Projection;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Projection\Projector;
use PHPUnit\Framework\TestCase;

final class ProjectorTest extends TestCase
{
    private \stdClass $model;
    private Projector $projector;

    protected function setUp(): void
    {
        $this->model = new \stdClass();
        $this->projector = new class ($this->model) extends Projector
        {
            private \stdClass $model;

            public function __construct(\stdClass $model)
            {
                $this->model = $model;
            }

            protected function applyBaseEvent(BaseEvent $event): void
            {
                $this->model->foo = $event->getPayload()['foo'];
            }
        };
    }

    public function testInvokeDispatchesEventToHandlerMethod(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);

        ($this->projector)(BaseEvent::occur($aggregateRootId, ['foo' => 'bar']));

        self::assertSame('bar', $this->model->foo);
    }
}
