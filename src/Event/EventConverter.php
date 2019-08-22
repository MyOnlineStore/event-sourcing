<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface EventConverter
{
    /**
     * @return mixed[]
     */
    public function convertToArray(Event $event): array;

    /**
     * @param mixed[] $data
     */
    public function createFromArray(array $data): Event;
}
