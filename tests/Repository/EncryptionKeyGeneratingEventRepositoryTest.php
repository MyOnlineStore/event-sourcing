<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Encryption\KeyGenerator;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EncryptionKeyGeneratingEventRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Repository\MetadataRepository;
use PHPUnit\Framework\TestCase;

final class EncryptionKeyGeneratingEventRepositoryTest extends TestCase
{
    /** @var EventRepository */
    private $innerRepository;

    /** @var KeyGenerator */
    private $keyGenerator;

    /** @var MetadataRepository */
    private $metadataRepository;

    /** @var EncryptionKeyGeneratingEventRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new EncryptionKeyGeneratingEventRepository(
            $this->innerRepository = $this->createMock(EventRepository::class),
            $this->keyGenerator = $this->createMock(KeyGenerator::class),
            $this->metadataRepository = $this->createMock(MetadataRepository::class),
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
        $streamName = 'event_stream';
        $aggregateId = $this->createMock(AggregateRootId::class);
        $stream = new Stream([], $streamMetadata = new StreamMetadata([]));

        $this->keyGenerator->expects(self::once())
            ->method('generate')
            ->willReturn('bar');

        $this->metadataRepository->expects(self::once())
            ->method('save')
            ->with($streamName, $aggregateId, $keyMetadata = $streamMetadata->withEncryptionKey('bar'));

        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($streamName, $aggregateId, $stream->withMetadata($keyMetadata));

        $this->repository->appendTo($streamName, $aggregateId, $stream);
    }

    public function testLoad(): void
    {
        $streamName = 'event_stream';
        $aggregateId = $this->createMock(AggregateRootId::class);
        $stream = new Stream([], $streamMetadata = new StreamMetadata([]));

        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($streamName, $aggregateId, $streamMetadata)
            ->willReturn($stream);

        self::assertSame(
            $stream,
            $this->repository->load($streamName, $aggregateId, $streamMetadata)
        );
    }
}
