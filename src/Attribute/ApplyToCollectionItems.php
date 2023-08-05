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
        foreach ($attributes as $attribute) {
            if (!is_object($attribute)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Attribute must be an object, %s given',
                        gettype($attribute),
                    ),
                );
            }
            if ($attribute instanceof InitialValueCallback) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Attribute %s is not allowed in collection items',
                        InitialValueCallback::class,
                    ),
                );
            }
            if ($attribute instanceof TargetProperty) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Attribute %s is not allowed in collection items',
                        TargetProperty::class,
                    ),
                );
            }
        }
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
