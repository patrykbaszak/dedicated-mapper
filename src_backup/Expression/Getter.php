<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression;

class Getter
{
    public const SOURCE_VARIABLE_NAME = '{{sourceVariableName}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(string $sourceVariableName): string
    {
        return str_replace(self::SOURCE_VARIABLE_NAME, $sourceVariableName, $this->expression);
    }
}
