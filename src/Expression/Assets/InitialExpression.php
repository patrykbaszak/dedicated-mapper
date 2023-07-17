<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

class InitialExpression
{
    public const VARIABLE_NAME = '{{variableName}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(string $variableName): string
    {
        return str_replace(self::VARIABLE_NAME, $variableName, $this->expression);
    }
}
