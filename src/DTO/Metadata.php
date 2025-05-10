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
    ) {}
}
