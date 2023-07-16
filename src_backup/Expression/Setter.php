<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Setter
{
    public const TARGET_VARIABLE_NAME = '{{targetVariableName}}';
    public const GETTER_EXPRESSION = '{{getterExpression}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(string $targetVariableName, string $getterExpression): string
    {
        return str_replace(
            [self::TARGET_VARIABLE_NAME, self::GETTER_EXPRESSION],
            [$targetVariableName, $getterExpression],
            $this->expression
        );
    }
}
