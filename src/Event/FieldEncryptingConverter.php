<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Encryption\Encrypter;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;

final class FieldEncryptingConverter implements EventConverter
{
    /** @var Encrypter */
    private $encrypter;

    /** @var EventConverter */
    private $innerConverter;

    public function __construct(Encrypter $encrypter, EventConverter $innerConverter)
    {
        $this->encrypter = $encrypter;
        $this->innerConverter = $innerConverter;
    }

    /**
     * @return mixed[]
     *
     * @throws EncryptionFailed
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array
    {
        $data = $this->innerConverter->convertToArray($event, $streamMetadata);

        if (!$event instanceof FieldEncrypting) {
            return $data;
        }

        foreach ($event::getEncryptingFields() as $field) {
            if (isset($data['payload'][$field])) {
                $data['payload'][$field] = $this->encrypter->encrypt(
                    $streamMetadata->getEncryptionKey(),
                    $data['payload'][$field]
                );
            }
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event
    {
        if (!\is_subclass_of($eventName, FieldEncrypting::class)) {
            return $this->innerConverter->createFromArray($eventName, $data, $streamMetadata);
        }

        /** @var FieldEncrypting $eventName */
        /** @psalm-var class-string<FieldEncrypting> $eventName */

        foreach ($eventName::getEncryptingFields() as $field) {
            if (isset($data['payload'][$field])) {
                try {
                    $data['payload'][$field] = $this->encrypter->decrypt(
                        $streamMetadata->getEncryptionKey(),
                        $data['payload'][$field]
                    );
                } catch (EncryptionFailed $exception) {
                    $data['payload'][$field] = null;
                }
            }
        }

        return $this->innerConverter->createFromArray($eventName, $data, $streamMetadata);
    }
}
