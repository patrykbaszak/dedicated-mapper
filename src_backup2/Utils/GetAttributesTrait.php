<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Utils;

trait GetAttributesTrait
{
    public function getAttributeInstance(\ReflectionProperty|\ReflectionParameter $reflection, string $attributeClass): ?object
    {
        $attributes = $reflection->getAttributes($attributeClass);
        if (0 === count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * @return object[]
     */
    public function getAttributesInstances(\ReflectionProperty|\ReflectionParameter $reflection, string $attributeClass): array
    {
        $attributes = $reflection->getAttributes();

        $attributes = array_filter(
            $attributes,
            fn ($attribute) => $attribute->getName() === $attributeClass
                || is_subclass_of($attribute->getName(), $attributeClass)
        );

        if (0 === count($attributes)) {
            return [];
        }

        $output = [];
        foreach ($attributes as $attribute) {
            $output[] = $attribute->newInstance();
        }

        return $output;
    }
}
