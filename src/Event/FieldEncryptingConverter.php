<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Encryption\Encrypter;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;

final class FieldEncryptingConverter implements EventConverter
{
    private Encrypter $encrypter;
    private EventConverter $innerConverter;

    public function __construct(Encrypter $encrypter, EventConverter $innerConverter)
    {
        $this->encrypter = $encrypter;
        $this->innerConverter = $innerConverter;
    }

    /**
     * @inheritDoc
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array
    {
        $data = $this->innerConverter->convertToArray($event, $streamMetadata);

        if (!$event instanceof FieldEncrypting) {
            return $data;
        }

        foreach ($event::getEncryptingFields() as $field) {
            if (empty($data['payload'][$field]) || \is_array($data['payload'][$field])) {
                continue;
            }

            $data['payload'][$field] = $this->encrypter->encrypt(
                $streamMetadata->getEncryptionKey(),
                (string) $data['payload'][$field]
            );
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event
    {
        if (!\is_subclass_of($eventName, FieldEncrypting::class)) {
            return $this->innerConverter->createFromArray($eventName, $data, $streamMetadata);
        }

        foreach ($eventName::getEncryptingFields() as $field) {
            if (!isset($data['payload'][$field]) || \is_array($data['payload'][$field])) {
                continue;
            }

            try {
                $data['payload'][$field] = $this->encrypter->decrypt(
                    $streamMetadata->getEncryptionKey(),
                    (string) $data['payload'][$field]
                );
            } catch (EncryptionFailed) {
                $data['payload'][$field] = null;
            }
        }

        return $this->innerConverter->createFromArray($eventName, $data, $streamMetadata);
    }
}
