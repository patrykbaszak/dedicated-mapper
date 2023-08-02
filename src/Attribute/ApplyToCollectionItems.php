<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Attribute;

/**
 * Part of the mapping process.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ApplyToCollectionItems
{
    /**
     * @param object[] $attributes attributes to apply to each item of collection
     * @param mixed[]  $options    any options required but custom actions
     */
    public function __construct(
        public readonly array $attributes,
        public readonly array $options = [],
    ) {
    }

    /**
     * @param class-string|null $attribute
     *
     * @return object[]
     */
    public function getAttributes(?string $attribute): array
    {
        if (null === $attribute) {
            return $this->attributes;
        }

        return array_filter(
            $this->attributes,
            fn (object $attribute): bool => $attribute instanceof $attribute,
        );
    }
}
