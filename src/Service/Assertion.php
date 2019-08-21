<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Service;

use Assert\Assertion as BaseAssertion;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;

final class Assertion extends BaseAssertion
{
    /** @var string */
    protected static $exceptionClass = AssertionFailed::class;
}
