<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Assets;

class FinalExpression
{
    public function __construct(
        public string $expression
    ) {
    }

    public function toString(): string
    {
        return $this->expression;
    }
}
