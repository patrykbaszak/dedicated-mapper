<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SerializedNames
{
    /**
     * @param string[]  $names    property name in serialized resource, in order of priorities
     * @param mixed[] $options - any options required but custom actions
     */
    public function __construct(
        public readonly array $names,
        public readonly array $options = [],
    ) {
    }
}
