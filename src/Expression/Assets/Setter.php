<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

class Setter
{
    public const STATEMENTS_ORDER = [
        'isCollection',
        'hasFunction',
        'hasPathUsed',
        'isSimpleObject',
        'hasSimpleObjectDeconstructor',
        'isVarVariableUsed',
    ];

    public const GETTER_EXPRESSION = '{{getter}}';
    public const TARGET_VARIABLE = '{{target}}';
    public const SIMPLE_OBJECT_DECONSTRUCTOR = '{{simpleObjectDeconstructor}}';
    public const FUNCTION_DECLARATION = '{{function}}';
    public const FUNCTION_VARIABLE = '{{functionVariable}}';

    /** @param string[] $expressions */
    public function __construct(
        private array $expressions,
    ) {
    }

    public function getExpression(
        bool $isCollection,
        bool $hasFunction,
        bool $hasPathUsed,
        bool $isSimpleObject,
        bool $hasSimpleObjectDeconstructor,
        bool $isVarVariableUsed,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));
        $expression = $this->expressions[$key];

        return $expression;
    }
}
