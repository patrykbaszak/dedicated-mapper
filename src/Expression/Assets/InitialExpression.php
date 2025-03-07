<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Assets;

class InitialExpression implements \Stringable
{
    public function __construct(
        public string $expression
    ) {
    }

    public function toString(): string
    {
        return $this->expression;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
