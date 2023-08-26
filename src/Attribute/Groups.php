<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Groups
{
    /**
     * @param string[] $groups   - list of groups to which this property belongs
     * @param mixed[] $options  - any options required but custom actions
     */
    public function __construct(
        public readonly array $groups,
        public readonly array $options = [],
    ) {
    }
}