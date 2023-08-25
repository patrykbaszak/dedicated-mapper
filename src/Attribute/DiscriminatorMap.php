<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DiscriminatorMap
{
    /**
     * @param string  $typeProperty
     * @param array<string,string>  $mapping
     * @param mixed[] $options - any options required but custom actions
     */
    public function __construct(
        public readonly string $typeProperty,
        public readonly array $mapping,
        public readonly array $options = [],
    ) {
    }
}
