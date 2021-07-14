<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Projection;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Tests\Mock\BaseProjector;
use PHPUnit\Framework\TestCase;

final class ProjectorTest extends TestCase
{
    private \stdClass $model;
    private BaseProjector $projector;

    protected function setUp(): void
    {
        $this->model = new \stdClass();
        $this->projector = new BaseProjector($this->model);
    }

    public function testInvokeDispatchesEventToHandlerMethod(): void
    {
        ($this->projector)(
            BaseEvent::occur(
                $this->createMock(AggregateRootId::class),
                ['foo' => 'bar']
            )
        );

        self::assertSame('bar', $this->model->foo);
    }
}
