<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

class Getter
{
    public const STATEMENTS_ORDER = [
        'isSimpleObject',
        'throwExceptionOnMissingRequiredValue',
        'hasDefaultValue',
        'hasCallbacks',
        'hasValueNotFoundCallbacks',
    ];

    public const SOURCE_VARIABLE_NAME = '{{source}}';
    public const SETTER_EXPRESSION = '{{setter}}';
    public const DEFAULT_VALUE_EXPRESSION = '{{defaultValue}}';
    public const SIMPLE_OBJECT_EXPRESSION = '{{simpleObject}}';
    public const CALLBACKS_EXPRESSION = '{{callbacks}}';
    public const VALUE_NOT_FOUND_EXPRESSIONS = '{{valueNotFoundCallbacks}}';

    public readonly bool $isVarVariableUsed;

    public function __construct(
        private array $expressions,
    ) {}

    public function getSimpleGetter(): string
    {
        return $this->expressions['basic'];
    }

    public function getExpression(
        $isSimpleObject,
        $throwExceptionOnMissingRequiredValue,
        $hasDefaultValue,
        $hasCallbacks,
        $hasValueNotFoundCallbacks,
    ): string {
        $key = implode('', array_map(fn($statement) => (int) $$statement, self::STATEMENTS_ORDER));

        $expression = $this->expressions[$key];

        if (strpos($expression, Expression::VAR_VARIABLE) !== false) {
            $this->isVarVariableUsed = true;
        }

        return $expression;
    }
}
