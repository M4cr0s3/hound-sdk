<?php

namespace Macrose\Hound\Filter;

use Macrose\Hound\DTO\Event;

interface EventFilterInterface
{
    public function process(Event $event): ?Event;
    public function setNext(EventFilterInterface $handler): EventFilterInterface;
    public function getNext(): ?EventFilterInterface;
}