<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TargetProperty
{
    /**
     * @param string  $name    property name in matching resource
     * @param mixed[] $options - any options required but custom actions
     */
    public function __construct(
        public readonly string $name,
        public readonly array $options = [],
    ) {
    }
}
