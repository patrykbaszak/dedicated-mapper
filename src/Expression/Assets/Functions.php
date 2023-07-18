<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

class Functions
{
    public const SOURCE_TYPE = '{{sourceType}}';
    public const SOURCE_NAME = '{{sourceName}}';
    public const TARGET_TYPE = '{{targetType}}';
    public const TARGET_NAME = '{{targetName}}';
    public const INITIAL_EXPRESSION = '{{initialExpression}}';
    public const EXPRESSIONS = '{{expressions}}';
    public const FINAL_EXPRESSION = '{{finalExpression}}';
    public const USE_STATEMENTS = '{{useStatements}}';
    public const PATH_TYPE = '{{pathType}}';
    public const PATH_NAME = '{{pathName}}';

    /** @param string[] $functions */
    public function __construct(
        private array $functions = [],
    ) {
    }

    public function getSimpleFunction(): string
    {
        return $this->functions['basic'];
    }

    public function getFunction(
        bool $hasPath,
        bool $hasUseStatements,
        bool $hasInitialExpression,
        bool $hasFinalExpression,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));

        return $this->functions[$key];
    }
}