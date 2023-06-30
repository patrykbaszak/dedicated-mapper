<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Function_
{
    public const FUNCTION_VARIABLE_NAME = '{{functionVariableName}}';
    public const SOURCE_VARIABLE_NAME = '{{originVariableName}}';
    public const TARGET_VARIABLE_NAME = '{{outputVariableName}}';
    public const FUNCTION_BODY = '{{functionBody}}';
    public const USE_STATEMENTS = '{{useStatements}}';

    public function __construct(
        public string $expression
    ) {}

    public function toString(
        string $functionVariableName,
        string $originVariableName,
        string $outputVariableName,
        string $functionBody,
        string $useStatements,
    ): string {
        return str_replace(
            [
                self::FUNCTION_VARIABLE_NAME,
                self::SOURCE_VARIABLE_NAME,
                self::TARGET_VARIABLE_NAME,
                self::FUNCTION_BODY,
                self::USE_STATEMENTS,
            ], 
            [
                $functionVariableName,
                $originVariableName,
                $outputVariableName,
                $functionBody,
                $useStatements,
            ], 
            $this->expression
        );
    }
}
