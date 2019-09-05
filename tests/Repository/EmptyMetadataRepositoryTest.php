<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EmptyMetadataRepository;
use PHPUnit\Framework\TestCase;

final class EmptyMetadataRepositoryTest extends TestCase
{
    /** @var EmptyMetadataRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new EmptyMetadataRepository();
    }

    public function testLoad(): void
    {
        self::assertEquals(
            new StreamMetadata([]),
            $this->repository->load('stream', $this->createMock(AggregateRootId::class))
        );
    }

    public function testSaveDoesNothing(): void
    {
        $aggregateId = $this->createMock(AggregateRootId::class);

        $this->repository->save('stream', $aggregateId, new StreamMetadata(['foo' => 'bar']));

        self::assertEquals(
            new StreamMetadata([]),
            $this->repository->load('stream', $this->createMock(AggregateRootId::class))
        );
    }
}
