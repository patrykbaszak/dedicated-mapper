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

    public bool $isVarVariableUsed;

    /** @param string[] $expressions */
    public function __construct(
        private array $expressions,
    ) {
    }

    public function getSimpleGetter(): string
    {
        return $this->expressions['basic'];
    }

    public function getExpression(
        bool $isSimpleObject,
        bool $throwExceptionOnMissingRequiredValue,
        bool $hasDefaultValue,
        bool $hasCallbacks,
        bool $hasValueNotFoundCallbacks,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));

        $expression = $this->expressions[$key];
        $this->isVarVariableUsed = false !== strpos($expression, Expression::VAR_VARIABLE);

        return $expression;
    }
}
