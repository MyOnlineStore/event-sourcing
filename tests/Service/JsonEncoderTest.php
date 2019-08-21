<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Service;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Encoder;
use MyOnlineStore\EventSourcing\Service\JsonEncoder;
use PHPUnit\Framework\TestCase;

final class JsonEncoderTest extends TestCase
{
    /** @var Encoder */
    private $encoder;

    protected function setUp(): void
    {
        $this->encoder = new JsonEncoder();
    }

    public function testEncode(): void
    {
        self::assertSame('{"foo":"bar"}', $this->encoder->encode(['foo' => 'bar']));
    }

    public function testEncodeFailed(): void
    {
        $this->expectException(EncodingFailed::class);
        $this->encoder->encode(\fopen('php://memory', 'rb'));
    }

    public function testDecode(): void
    {
        self::assertSame(['foo' => 'bar'], $this->encoder->decode('{"foo":"bar"}'));
    }

    public function testDecodeFailed(): void
    {
        $this->expectException(EncodingFailed::class);
        $this->encoder->decode('{');
    }
}
