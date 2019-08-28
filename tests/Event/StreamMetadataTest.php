<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use PHPUnit\Framework\TestCase;

final class StreamMetadataTest extends TestCase
{
    public function testEmptyMetadata(): void
    {
        $metadata = new StreamMetadata([]);

        self::assertSame([], $metadata->getMetadata());
        self::assertSame('', $metadata->getEncryptionKey());
        self::assertFalse($metadata->hasEncryptionKey());
    }

    public function testWithEncryptionKeyConstructed(): void
    {
        $metadata = new StreamMetadata(['encryption_key' => 'foo']);

        self::assertSame(['encryption_key' => 'foo'], $metadata->getMetadata());
        self::assertSame('foo', $metadata->getEncryptionKey());
        self::assertTrue($metadata->hasEncryptionKey());
    }

    public function testWithEncryptionKeyCreatesNewObjectWithNewKey(): void
    {
        $metadata = new StreamMetadata(['encryption_key' => 'foo']);
        $newMetadata = $metadata->withEncryptionKey('bar');

        self::assertSame('foo', $metadata->getEncryptionKey());
        self::assertSame('bar', $newMetadata->getEncryptionKey());
    }
}
