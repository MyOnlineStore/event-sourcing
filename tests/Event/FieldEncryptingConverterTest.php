<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use Mockery\MockInterface;
use MyOnlineStore\EventSourcing\Event\EncryptingFields;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\FieldEncrypting;
use MyOnlineStore\EventSourcing\Event\FieldEncryptingConverter;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use PHPUnit\Framework\TestCase;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Exception\InvalidArgumentException;

final class FieldEncryptingConverterTest extends TestCase
{
    /** @var BlockCipher */
    private $blockCipher;

    /** @var FieldEncryptingConverter */
    private $converter;

    /** @var EventConverter */
    private $innerConverter;

    /** @var StreamMetadata */
    private $streamMetadata;

    protected function setUp(): void
    {
        $this->converter = new FieldEncryptingConverter(
            $this->blockCipher = $this->createMock(BlockCipher::class),
            $this->innerConverter = $this->createMock(EventConverter::class)
        );

        $this->streamMetadata = new StreamMetadata(['encryption_key' => 'foo']);
    }

    public function testConvertToArrayDoesNotEncryptIfNotFieldEncryptingEvent(): void
    {
        $event = $this->createMock(Event::class);

        $this->blockCipher->expects(self::never())->method('encrypt');

        $this->innerConverter->expects(self::once())
            ->method('convertToArray')
            ->with($event, $this->streamMetadata)
            ->willReturn(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->converter->convertToArray($event, $this->streamMetadata));
    }

    public function testConvertToArrayEncryptsEncryptingEvents(): void
    {
        /** @var Event|FieldEncrypting|MockInterface $event */
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));

        $this->innerConverter->expects(self::once())
            ->method('convertToArray')
            ->with($event, $this->streamMetadata)
            ->willReturn(['payload' => ['foo' => 'bar']]);

        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(new EncryptingFields(['foo']));

        $this->blockCipher->expects(self::once())
            ->method('encrypt')
            ->with('bar')
            ->willReturn('encrypted_bar');

        self::assertSame(
            ['payload' => ['foo' => 'encrypted_bar']],
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

        $this->blockCipher->expects(self::never())->method('decrypt');

        self::assertSame($event, $this->converter->createFromArray($eventName, $data, $this->streamMetadata));
    }

    public function testCreateFromArrayDecryptsEncryptingEvents(): void
    {
        /** @var Event|FieldEncrypting|MockInterface $event */
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        $eventName = \get_class($event);

        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(new EncryptingFields(['foo']));

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
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
        /** @var Event|FieldEncrypting|MockInterface $event */
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        $eventName = \get_class($event);

        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(new EncryptingFields(['foo']));

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
            ->willThrowException(new InvalidArgumentException());

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

    public function testCreateFromArrayDoesNotErrorIfKeyEmptyAndSetsFieldToNullIfCantDecrypt(): void
    {
        /** @var Event|FieldEncrypting|MockInterface $event */
        $event = \Mockery::mock(\sprintf('%s, %s', FieldEncrypting::class, Event::class));
        $eventName = \get_class($event);

        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo')
            ->willThrowException(new InvalidArgumentException());

        $event->shouldReceive('getEncryptingFields')
            ->andReturn(new EncryptingFields(['foo']));

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
            ->willThrowException(new InvalidArgumentException());

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
}
