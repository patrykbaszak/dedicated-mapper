<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

class AttributeReflection
{
    public function __construct(
        /** 
         * @var ClassReflection|CollectionReflection|PropertyReflection|SimpleObjectReflection $parent each attribute must have resource
         */
        protected ClassReflection|CollectionReflection|PropertyReflection|SimpleObjectReflection $parent,

        /**
         * @var ArrayObject<object{"class": string, "arguments": mixed[]}> $attributes
         */
        protected ArrayObject $attributes,
    ) {}

    /**
     * @return ClassReflection|CollectionReflection|PropertyReflection|SimpleObjectReflection
     */
    public function getParent(): ClassReflection|CollectionReflection|PropertyReflection|SimpleObjectReflection
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
