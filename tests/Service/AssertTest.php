<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Service;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assert;
use PHPUnit\Framework\TestCase;

final class AssertTest extends TestCase
{
    public function testReportInvalidArgumentThrowsCorrectException(): void
    {
        $this->expectException(AssertionFailed::class);
        Assert::isEmpty('foo');
    }
}
