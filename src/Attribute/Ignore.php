<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Ignore
{
    /**
     * @param mixed[] $options - any options required but custom actions
     */
    public function __construct(
        public readonly array $options = [],
    ) {
    }
}
