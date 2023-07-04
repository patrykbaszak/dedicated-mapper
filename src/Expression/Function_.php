<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Function_
{
    public const SOURCE_VARIABLE_NAME = '{{originVariableName}}';
    public const TARGET_VARIABLE_NAME = '{{outputVariableName}}';
    public const FUNCTION_BODY = '{{functionBody}}';
    public const USE_STATEMENTS = '{{useStatements}}';

    public function __construct(
        public string $expression
    ) {
    }

    public function toString(
        string $originVariableName,
        string $outputVariableName,
        string $functionBody,
        string $useStatements,
    ): string {
        return str_replace(
            [
                self::SOURCE_VARIABLE_NAME,
                self::TARGET_VARIABLE_NAME,
                self::FUNCTION_BODY,
                self::USE_STATEMENTS,
            ],
            [
                $originVariableName,
                $outputVariableName,
                $functionBody,
                $useStatements,
            ],
            $this->expression
        );
    }
}
