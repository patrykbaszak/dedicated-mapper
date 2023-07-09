<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Attribute;

/**
 * Part of the mapping process.
 * Use is if You got class like DateTime or ArrayObject but Your own.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SimpleObject
{
    /**
     * @param string              $staticConstructor name of static method to create object,
     *                                               if `null` then constructor will be used
     * @param array<string,mixed> $namedArguments    arguments passed to constructor or static method
     * @param mixed[]             $options           any options required but custom actions
     */
    public function __construct(
        public readonly ?string $staticConstructor = null,
        public readonly ?string $nameOfArgument = null,
        public readonly array $namedArguments = [],
        public readonly array $options = [],
    ) {
    }
}
