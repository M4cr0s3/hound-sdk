<?php

namespace Macrose\Hound\Filter;

use Macrose\Hound\DTO\Event;

abstract class AbstractEventFilter implements EventFilterInterface
{
    private ?EventFilterInterface $nextHandler = null;

    public function setNext(EventFilterInterface $handler): EventFilterInterface
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function getNext(): ?EventFilterInterface
    {
        return $this->nextHandler;
    }

    public function process(Event $event): ?Event
    {
        $processedEvent = $this->handle($event);

        if ($processedEvent === null) {
            return null;
        }

        if ($this->nextHandler !== null) {
            return $this->nextHandler->process($processedEvent);
        }

        return $processedEvent;
    }

    abstract protected function handle(Event $event): ?Event;
}