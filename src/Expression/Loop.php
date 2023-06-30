<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Loop
{
    public const TARGET_VARIABLE_NAME = '{{outputVariableName}}';
    public const ITERABLE_VARIABLE_NAME = '{{iterableVariableName}}';
    public const SOURCE_VARIABLE_NAME = '{{sourceVariableName}}';
    public const CODE = '{{code}}';

    public function __construct(
        public string $expression
    ) {}

    public function toString(
        string $outputVariableName,
        string $iterableVariableName,
        string $sourceVariableName,
        string $code,
    ): string {
        return str_replace(
            [
                self::TARGET_VARIABLE_NAME,
                self::ITERABLE_VARIABLE_NAME,
                self::SOURCE_VARIABLE_NAME,
                self::CODE,
            ], 
            [
                $outputVariableName,
                $iterableVariableName,
                $sourceVariableName,
                $code,
            ], 
            $this->expression
        );
    }
}
