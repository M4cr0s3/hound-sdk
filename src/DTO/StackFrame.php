<?php

declare(strict_types=1);

namespace Macrose\Hound\DTO;

final readonly class StackFrame
{
    public function __construct(
        public string $file,
        public int $line,
        public string $function,
        public string $class,
        public string $type,
        public CodeSnippet $codeSnippet,
    ) {}
}
