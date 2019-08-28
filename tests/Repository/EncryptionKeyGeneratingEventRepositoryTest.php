<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EncryptionKeyGeneratingEventRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Service\KeyGenerator;
use PHPUnit\Framework\TestCase;

final class EncryptionKeyGeneratingEventRepositoryTest extends TestCase
{
    /** @var EventRepository */
    private $innerRepository;

    /** @var KeyGenerator */
    private $keyGenerator;

    /** @var EncryptionKeyGeneratingEventRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new EncryptionKeyGeneratingEventRepository(
            $this->innerRepository = $this->createMock(EventRepository::class),
            $this->keyGenerator = $this->createMock(KeyGenerator::class),
        );
    }

    public function testDoesNothingIfEncryptionKeyAlreadyExists(): void
    {
        $streamName = 'event_stream';
        $aggregateId = $this->createMock(AggregateRootId::class);
        $streamMetadata = new StreamMetadata(['encryption_key' => 'foo']);
        $stream = new Stream([], $streamMetadata);

        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($streamName, $aggregateId, $stream);

        $this->repository->appendTo($streamName, $aggregateId, $stream);
    }

    public function testGeneratesNewEncryptionKeyIfNoneExists(): void
    {
        $repository = $this->getMockBuilder(EncryptionKeyGeneratingEventRepository::class)
            ->setConstructorArgs([$this->innerRepository, $this->keyGenerator])
            ->onlyMethods(['updateMetadata'])
            ->getMock();

        $streamName = 'event_stream';
        $aggregateId = $this->createMock(AggregateRootId::class);
        $streamMetadata = new StreamMetadata([]);
        $stream = new Stream([], $streamMetadata);

        $this->keyGenerator->expects(self::once())
            ->method('generate')
            ->willReturn('bar');

        $repository->expects(self::once())
            ->method('updateMetadata')
            ->with($streamName, $aggregateId, $keyMetadata = $streamMetadata->withEncryptionKey('bar'));

        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($streamName, $aggregateId, $stream->withMetadata($keyMetadata));

        $repository->appendTo($streamName, $aggregateId, $stream);
    }
}
