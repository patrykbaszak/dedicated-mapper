<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

class Setter
{
    public const STATEMENTS_ORDER = [
        'isCollection',
        'hasFunction',
        'hasPathUsed',
        'isVarVariableUsed',
        'hasDeconstructorCall',
    ];

    /**
     * @param string[]                    $expressionTemplates
     * @param array<array<string,string>> $expressions
     */
    public function __construct(
        private array $expressionTemplates,
        private array $expressions
    ) {
    }

    public function getExpressionTemplate(
        bool $isCollection,
        bool $hasFunction,
        bool $hasPathUsed,
        bool $isVarVariableUsed,
        bool $hasDeconstructorCall,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));

        return $this->expressionTemplates[$key];
    }

    /**
     * @return array<string,string>
     */
    public function getExpressions(
        bool $isCollection,
        bool $hasFunction,
        bool $hasPathUsed,
        bool $isVarVariableUsed,
        bool $hasDeconstructorCall,
    ): array {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));

        return $this->expressions[$key];
    }
}
