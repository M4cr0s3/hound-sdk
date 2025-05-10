<?php

declare(strict_types=1);

namespace Macrose\Hound\DTO;

use Macrose\Hound\Attributes\SerializedName;

final readonly class Event
{
    public function __construct(
        #[SerializedName('event_id')]
        public string $eventId,
        public string $message,
        public string $level,
        public string $type,
        public int $count,
        public Metadata $metadata,
        public string $environment,
        public string $release,
    ) {}
}
