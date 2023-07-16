<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Function_
{
    public const SOURCE_VARIABLE_TYPE = '{{originVariableType}}';
    public const SOURCE_VARIABLE_NAME = '{{originVariableName}}';
    public const TARGET_VARIABLE_NAME = '{{outputVariableName}}';
    public const FUNCTION_BODY = '{{functionBody}}';
    public const RETURN_TYPE = '{{returnType}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(
        string $originVariableName,
        string $outputVariableName,
        string $functionBody,
        string $originVariableType = 'mixed',
        string $returnType = null,
    ): string {
        return str_replace(
            [
                self::SOURCE_VARIABLE_TYPE,
                self::SOURCE_VARIABLE_NAME,
                self::TARGET_VARIABLE_NAME,
                self::FUNCTION_BODY,
                self::RETURN_TYPE,
            ],
            [
                $originVariableType,
                $originVariableName,
                $outputVariableName,
                $functionBody,
                $returnType && ($returnType = trim($returnType)) ? (preg_match('/^: .+$/', $returnType) ? $returnType : ": {$returnType}") : '',
            ],
            $this->expression
        );
    }
}
