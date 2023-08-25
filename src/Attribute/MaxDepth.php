<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MaxDepth
{
    /**
     * @param string[]  $maxDepth
     * @param mixed[] $options - any options required but custom actions
     */
    public function __construct(
        public readonly int $maxDepth,
        public readonly array $options = [],
    ) {
    }
}
