<?php

declare(strict_types=1);

namespace Macrose\Hound\DTO;

final readonly class CodeSnippet
{
    public function __construct(
        /** @var string[] */
        public array $preContext,
        public string $contextLine,
        /** @var string[] */
        public array $postContext,
    ) {}
}
