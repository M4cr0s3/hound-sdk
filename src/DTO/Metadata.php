<?php

declare(strict_types=1);

namespace Macrose\Hound\DTO;

final readonly class Metadata
{
    public function __construct(
        public int $fingerprint,
        /** @var StackFrame[] */
        public array $stacktrace,
        public string $file,
        public int $line,
        public string $function,
        public string $class,
        public string $type,
        /** @var array<string, mixed> */
        public array $context = [],
    ) {}

    public function withContext(array $context): self
    {
        return new self(
            $this->fingerprint,
            $this->stacktrace,
            $this->file,
            $this->line,
            $this->function,
            $this->class,
            $this->type,
            array_merge($this->context, $context)
        );
    }
}
