<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Listener;

use MyOnlineStore\EventSourcing\Listener\Attribute\Listener;

trait AttributeListenerAware
{
    /** @var array<string, array<string, list<callable>>> */
    private array $listeners = [];

    /** @return array<string, list<callable>> */
    protected function getListeners(string $event): array
    {
        $objectHash = \spl_object_hash($this);

        if (!isset($this->listeners[$objectHash]) || empty($this->listeners[$objectHash])) {
            $reflection = new \ReflectionObject($this);

            foreach ($reflection->getMethods() as $method) {
                foreach ($method->getAttributes(Listener::class) as $listenerAttribute) {
                    $listener = $listenerAttribute->newInstance();

                    if (!isset($this->listeners[$objectHash][$listener->event])) {
                        $this->listeners[$objectHash][$listener->event] = [];
                    }

                    $this->listeners[$objectHash][$listener->event][] = [$this, $method->getName()];
                }
            }
        }

        /** @psalm-var array<string, list<callable>> */
        return $this->listeners[$objectHash][$event] ?? [];
    }
}
