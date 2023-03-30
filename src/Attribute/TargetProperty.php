<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class TargetProperty
{
    /**
     * @param string $name property name in matching resource
     */
    public function __construct(
        public readonly string $name,
        public readonly array $options = [],
    ) {}
}
