<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression;

/**
 * @example <code>
 * if (isset(${{variableName}} = {{getter}})) {
 *   {{code}}
 * }
 * </code>
 */
class Statement
{
    public const SOURCE_VARIABLE_NAME = '{{sourceVariableName}}';
    public const VARIABLE_NAME = '{{variableName}}';
    public const GETTER = '{{getter}}';
    public const CODE = '{{code}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(
        string $sourceVariableName,
        string $variableName,
        string $getter,
        string $code
    ): string {
        return str_replace(
            [self::SOURCE_VARIABLE_NAME, self::VARIABLE_NAME, self::GETTER, self::CODE],
            [$sourceVariableName, $variableName, $getter, $code],
            $this->expression
        );
    }
}
