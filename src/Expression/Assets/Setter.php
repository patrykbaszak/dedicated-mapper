<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

class Setter
{
    public const STATEMENTS_ORDER = [
        'isSimpleObject',
        'hasSimpleObjectDeconstructor',
        'isVarVariableUsed',
    ];

    public const GETTER_EXPRESSION = '{{getter}}';
    public const TARGET_VARIABLE = '{{target}}';
    public const SIMPLE_OBJECT_DECONSTRUCTOR = '{{simpleObjectDeconstructor}}';

    public function __construct(
        private array $expressions,
    ) {
    }

    public function getExpression(
        $isSimpleObject,
        $hasSimpleObjectDeconstructor,
        $isVarVariableUsed,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));
        $expression = $this->expressions[$key];

        return $expression;
    }
}
