<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

use Assert\AssertionFailedException;

final class AssertionFailed extends EventSourcingException implements AssertionFailedException
{
    /** @var string|null */
    private $propertyPath;

    /** @var mixed */
    private $value;

    /** @var mixed[] */
    private $constraints;

    /**
     * @param mixed   $value
     * @param mixed[] $constraints
     */
    public function __construct(string $message, int $code, ?string $propertyPath, $value, array $constraints = [])
    {
        parent::__construct($message, $code);

        $this->propertyPath = $propertyPath;
        $this->value = $value;
        $this->constraints = $constraints;
    }

    public function getPropertyPath(): ?string
    {
        return $this->propertyPath;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
