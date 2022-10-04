<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

final class StreamMetadata
{
    /** @param array<string, string> $metadata */
    public function __construct(private array $metadata)
    {
    }

    public function getEncryptionKey(): string
    {
        return $this->metadata['encryption_key'] ?? '';
    }

    /** @return array<string, string> */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function hasEncryptionKey(): bool
    {
        return !empty($this->metadata['encryption_key']);
    }

    public function withEncryptionKey(string $key): self
    {
        $copy = clone $this;
        $copy->metadata['encryption_key'] = $key;

        return $copy;
    }
}
