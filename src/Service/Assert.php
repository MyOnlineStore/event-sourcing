<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use Webmozart\Assert\Assert as WebmozartAssert;

final class Assert extends WebmozartAssert
{
    /**
     * @inheritDoc
     *
     * @throws AssertionFailed
     *
     * @psalm-pure
     */
    protected static function reportInvalidArgument($message): void
    {
        throw new AssertionFailed($message);
    }
}
