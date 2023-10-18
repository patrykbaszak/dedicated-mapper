<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use PBaszak\DedicatedMapper\Reflection\Type\CollectionType;
use PBaszak\DedicatedMapper\Reflection\Type\SimpleObjectType;

class AttributeReflection
{
    public function toArray(): array
    {
        return array_map(
            fn (object $attribute) => [
                'class' => $attribute->class,
                'arguments' => $attribute->arguments,
                'instance' => var_export($attribute->instance, true),
            ],
            $this->attributes->getArrayCopy()
        );
    }

    public function __construct(
        /** 
         * @var ClassReflection|CollectionType|PropertyReflection|SimpleObjectType $parent each attribute must have resource
         */
        protected ClassReflection|CollectionType|PropertyReflection|SimpleObjectType $parent,

        /**
         * @var ArrayObject<object{"class": string, "arguments": mixed[]}> $attributes
         */
        protected ArrayObject $attributes,
    ) {
    }

    /**
     * @return ClassReflection|CollectionType|PropertyReflection|SimpleObjectType
     */
    public function getParent(): ClassReflection|CollectionType|PropertyReflection|SimpleObjectType
    {
        return $this->parent;
    }

    /**
     * @return ArrayObject<object{"class": string, "arguments": mixed[]}>
     */
    public function getAttributes(): ArrayObject
    {
        return $this->attributes;
    }

    /**
     * @param class-string $class
     * 
     * @return null|object{"class": string, "arguments": mixed[]}
     */
    public function getAttribute(string $class): ?object
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->class === $class) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @param class-string $class
     * 
     * @return bool
     */
    public function hasAttribute(string $class): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->class === $class) {
                return true;
            }
        }

        return false;
    }
}
