<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

class InitialExpression
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
