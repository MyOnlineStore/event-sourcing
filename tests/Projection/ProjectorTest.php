<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Projection;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Projection\Projector;
use MyOnlineStore\EventSourcing\Projection\ReadModel;
use MyOnlineStore\EventSourcing\Projection\ReadModelRepository;
use PHPUnit\Framework\TestCase;

final class ProjectorTest extends TestCase
{
    /** @var AggregateRoot */
    private $projector;

    /** @var ReadModelRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ReadModelRepository::class);

        // phpcs:disable
        $this->projector = new class($this->repository) extends Projector
        {
            protected function applyBaseEvent(BaseEvent $event): void
            {
                $model = $this->repository->load($event->getAggregateId());
                $model->foo = $event->getPayload()['foo'];

                $this->repository->save($model);
            }
        };
        // phpcs:enable
    }

    public function testInvokeDispatchesEventToHandlerMethod(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $model = $this->createMock(ReadModel::class);

        $this->repository->expects(self::once())
            ->method('load')
            ->with($aggregateRootId)
            ->willReturn($model);

        $this->repository->expects(self::once())->method('save')->with($model);

        ($this->projector)(BaseEvent::occur($aggregateRootId, ['foo' => 'bar']));
    }
}
