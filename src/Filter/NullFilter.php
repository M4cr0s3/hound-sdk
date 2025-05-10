<?php

declare(strict_types=1);

namespace Macrose\Hound\Filter;

use Macrose\Hound\DTO\Event;

final class NullFilter implements EventFilterInterface
{
    public function process(Event $event): ?Event { return $event; }
    public function setNext(EventFilterInterface $handler): EventFilterInterface { return $this; }
    public function getNext(): ?EventFilterInterface { return null; }
}