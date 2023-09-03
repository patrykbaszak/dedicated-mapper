<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use ArrayObject;
use PBaszak\DedicatedMapper\Reflection\AttributeReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;

class CollectionType implements TypeInterface
{
    public function __construct(
        /** 
         * @var null|PropertyReflection|TypeInterface $parent  
         * collection can be nested in another collection, if `null` then it is root collection
         */
        protected null|PropertyReflection|TypeInterface $parent,

        /**
         * @var ArrayObject<TypeInterface> $children
         */
        protected ArrayObject $children,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
    ) {}

    /**
     * @return null|PropertyReflection|TypeInterface
     */
    public function getParent(): null|PropertyReflection|TypeInterface
    {
        return $this->parent;
    }

    /**
     * @return ArrayObject<TypeInterface>
     */
    public function getChildren(): ArrayObject
    {
        return $this->children;
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }
}
