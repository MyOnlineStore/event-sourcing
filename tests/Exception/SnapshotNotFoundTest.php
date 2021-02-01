<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;
use PHPUnit\Framework\TestCase;

final class SnapshotNotFoundTest extends TestCase
{
    public function testWithAggregateRootId(): void
    {
        $exception = SnapshotNotFound::withAggregateRootId(
            AggregateRootId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e')
        );

        self::assertStringContainsString(
            '8311db73-de57-4fb0-b8bc-84dc37296c1e',
            $exception->getMessage()
        );
    }
}
