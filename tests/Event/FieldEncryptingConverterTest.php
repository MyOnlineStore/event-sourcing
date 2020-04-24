<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Encryption\Encrypter;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\FieldEncrypting;
use MyOnlineStore\EventSourcing\Event\FieldEncryptingConverter;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FieldEncryptingConverterTest extends TestCase
{
    /** @var Encrypter&MockObject */
    private Encrypter $encrypter;
    private FieldEncryptingConverter $converter;
    /** @var EventConverter&MockObject */
    private EventConverter $innerConverter;
    private StreamMetadata $streamMetadata;

    protected function setUp(): void
    {
        $this->converter = new FieldEncryptingConverter(
            $this->encrypter = $this->createMock(Encrypter::class),
            $this->innerConverter = $this->createMock(EventConverter::class)
        );

        $this->streamMetadata = new StreamMetadata(['encryption_key' => 'foo']);
    }

    public function testConvertToArrayDoesNotEncryptIfNotFieldEncryptingEvent(): void
    {
        $event = $this->createMock(Event::class);

        $this->encrypter->expects(self::never())->method('encrypt');

        $this->innerConverter->expects(self::once())
            ->method('convertToArray')
            ->with($event, $this->streamMetadata)
            ->willReturn(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->converter->convertToArray($event, $this->streamMetadata));
    }

    public function testConvertToArrayEncryptsEncryptingEvents(): void
    {
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        \assert($event instanceof Event && $event instanceof FieldEncrypting);

        $this->innerConverter->expects(self::once())
            ->method('convertToArray')
            ->with($event, $this->streamMetadata)
            ->willReturn(['payload' => ['foo' => 'bar']]);

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(['foo']);

        $this->encrypter->expects(self::once())
            ->method('encrypt')
            ->with('foo', 'bar')
            ->willReturn('encrypted_bar');

        self::assertSame(
            ['payload' => ['foo' => 'encrypted_bar']],
            $this->converter->convertToArray($event, $this->streamMetadata)
        );
    }

    public function testConvertToArrayDoesNotEncryptEmptyFields(): void
    {
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        \assert($event instanceof Event && $event instanceof FieldEncrypting);

        $this->innerConverter->expects(self::once())
            ->method('convertToArray')
            ->with($event, $this->streamMetadata)
            ->willReturn(['payload' => ['foo' => null, 'bar' => '']]);

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(['foo', 'bar']);

        $this->encrypter->expects(self::never())->method('encrypt');

        self::assertSame(
            ['payload' => ['foo' => null, 'bar' => '']],
            $this->converter->convertToArray($event, $this->streamMetadata)
        );
    }

    public function testCreateFromArrayDoesNotDecryptIfNotFieldEncryptingEvent(): void
    {
        $eventName = Event::class;
        $data = ['foo' => 'bar'];

        $this->innerConverter->expects(self::once())
            ->method('createFromArray')
            ->with($eventName, $data, $this->streamMetadata)
            ->willReturn($event = $this->createMock(Event::class));

        $this->encrypter->expects(self::never())->method('decrypt');

        self::assertSame($event, $this->converter->createFromArray($eventName, $data, $this->streamMetadata));
    }

    public function testCreateFromArrayDecryptsEncryptingEvents(): void
    {
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        \assert($event instanceof Event && $event instanceof FieldEncrypting);
        $eventName = \get_class($event);

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(['foo']);

        $this->encrypter->expects(self::once())
            ->method('decrypt')
            ->with('foo', 'bar_encrypted')
            ->willReturn('bar');

        $this->innerConverter->expects(self::once())
            ->method('createFromArray')
            ->with($eventName, ['payload' => ['foo' => 'bar']], $this->streamMetadata)
            ->willReturn($event);

        self::assertSame(
            $event,
            $this->converter->createFromArray(
                $eventName,
                ['payload' => ['foo' => 'bar_encrypted']],
                $this->streamMetadata
            )
        );
    }

    public function testCreateFromArraySetsFieldToNullIfCantDecrypt(): void
    {
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        \assert($event instanceof Event && $event instanceof FieldEncrypting);
        $eventName = \get_class($event);

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(['foo']);

        $this->encrypter->expects(self::once())
            ->method('decrypt')
            ->with('foo', 'bar_encrypted')
            ->willThrowException(EncryptionFailed::toDecrypt());

        $this->innerConverter->expects(self::once())
            ->method('createFromArray')
            ->with($eventName, ['payload' => ['foo' => null]], $this->streamMetadata)
            ->willReturn($event);

        self::assertSame(
            $event,
            $this->converter->createFromArray(
                $eventName,
                ['payload' => ['foo' => 'bar_encrypted']],
                $this->streamMetadata
            )
        );
    }

    public function testCreateFromArraySkipsUnencryptedFields(): void
    {
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        \assert($event instanceof Event && $event instanceof FieldEncrypting);
        $eventName = \get_class($event);

        $event->shouldReceive('getEncryptingFields')->andReturn(['foo']);
        $this->encrypter->expects(self::never())->method('decrypt');

        $this->innerConverter->expects(self::once())
            ->method('createFromArray')
            ->with($eventName, ['payload' => ['bar' => 'bar_unencrypted']], $this->streamMetadata)
            ->willReturn($event);

        self::assertSame(
            $event,
            $this->converter->createFromArray(
                $eventName,
                ['payload' => ['bar' => 'bar_unencrypted']],
                $this->streamMetadata
            )
        );
    }
}
