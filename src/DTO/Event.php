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

    public function withMessage(string $message): self
    {
        return new self(
            $this->eventId,
            $message,
            $this->level,
            $this->type,
            $this->count,
            $this->metadata,
            $this->environment,
            $this->release
        );
    }

    public function withLevel(string $level): self
    {
        return new self(
            $this->eventId,
            $this->message,
            $level,
            $this->type,
            $this->count,
            $this->metadata,
            $this->environment,
            $this->release
        );
    }

    public function withMetadata(Metadata $metadata): self
    {
        return new self(
            $this->eventId,
            $this->message,
            $this->level,
            $this->type,
            $this->count,
            $metadata,
            $this->environment,
            $this->release
        );
    }
}
