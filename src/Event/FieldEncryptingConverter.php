<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use Zend\Crypt\BlockCipher;
use Zend\Crypt\Exception\InvalidArgumentException;

final class FieldEncryptingConverter implements EventConverter
{
    /** @var BlockCipher */
    private $blockCipher;

    /** @var EventConverter */
    private $innerConverter;

    public function __construct(BlockCipher $blockCipher, EventConverter $innerConverter)
    {
        $this->blockCipher    = $blockCipher;
        $this->innerConverter = $innerConverter;
    }

    /**
     * @return mixed[]
     *
     * @throws InvalidArgumentException
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array
    {
        $data = $this->innerConverter->convertToArray($event, $streamMetadata);

        if (!$event instanceof FieldEncrypting) {
            return $data;
        }

        $this->blockCipher->setKey($streamMetadata->getEncryptionKey());

        foreach ($event::getEncryptingFields() as $field) {
            if (isset($data['payload'][$field])) {
                $data['payload'][$field] = $this->blockCipher->encrypt($data['payload'][$field]);
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

        try {
            $this->blockCipher->setKey($streamMetadata->getEncryptionKey());
        } catch (InvalidArgumentException $exception) {
            // Key might have been removed: decryption will fail, setting the fields to null
        }

        /** @var FieldEncrypting $eventName */
        /** @psalm-var class-string<FieldEncrypting> $field */

        foreach ($eventName::getEncryptingFields() as $field) {
            if (isset($data['payload'][$field])) {
                try {
                    $data['payload'][$field] = $this->blockCipher->decrypt($data['payload'][$field]);
                } catch (\Throwable $exception) {
                    $data['payload'][$field] = null;
                }
            }
        }

        return $this->innerConverter->createFromArray($eventName, $data, $streamMetadata);
    }
}
