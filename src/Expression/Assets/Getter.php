<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

class Getter
{
    public const STATEMENTS_ORDER = [
        'hasDedicatedGetter',
        'throwExceptionOnMissingRequiredValue',
        'hasDefaultValue',
        'hasCallbacks',
        'hasValueNotFoundCallbacks',
        'isCollection'
    ];

    /**
     * @param string[] $expressionTemplates  
     * @param array<array<string,string>> $expressions
     */
    public function __construct(
        private array $expressionTemplates,
        private array $expressions
    ) {
    }

    public function getExpressionTemplate(
        bool $hasDedicatedGetter,
        bool $throwExceptionOnMissingRequiredValue,
        bool $hasDefaultValue,
        bool $hasCallbacks,
        bool $hasValueNotFoundCallbacks,
        bool $isCollection,
    ): string {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));

        return $this->expressionTemplates[$key];
    }

    /**
     * @return array<string,string>
     */
    public function getExpressions(
        bool $hasDedicatedGetter,
        bool $throwExceptionOnMissingRequiredValue,
        bool $hasDefaultValue,
        bool $hasCallbacks,
        bool $hasValueNotFoundCallbacks,
        bool $isCollection,
    ): array {
        $key = implode('', array_map(fn ($statement) => (int) $statement, func_get_args()));
        
        return $this->expressions[$key];
    }
}
