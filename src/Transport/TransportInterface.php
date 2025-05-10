<?php

declare(strict_types=1);

namespace Macrose\Hound\Transport;

use Macrose\Hound\DTO\Event;

interface TransportInterface
{
    public function send(Event $event): void;
}