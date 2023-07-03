<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

class Loop
{
    public const TARGET_VARIABLE_NAME = '{{outputVariableName}}';
    public const ITERABLE_GETTER = '{{iterableGetter}}';
    public const ITERABLE_SETTER = '{{iterableSetter}}';
    public const SOURCE_VARIABLE_NAME = '{{sourceVariableName}}';
    public const CODE = '{{code}}';

    public function __construct(
        public string $expression
    ) {}

    public function toString(
        string $outputVariableName,
        string $iterableGetter,
        string $sourceVariableName,
        string $code,
        string $iterableSetter,
    ): string {
        return str_replace(
            [
                self::TARGET_VARIABLE_NAME,
                self::ITERABLE_GETTER,
                self::SOURCE_VARIABLE_NAME,
                self::CODE,
                self::ITERABLE_SETTER
            ], 
            [
                $outputVariableName,
                $iterableGetter,
                $sourceVariableName,
                $code,
                $iterableSetter
            ], 
            $this->expression
        );
    }
}
