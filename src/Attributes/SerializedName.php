<?php

declare(strict_types=1);

namespace Macrose\Hound\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SerializedName
{
    public function __construct(
        public string $name
    ) {}
}
