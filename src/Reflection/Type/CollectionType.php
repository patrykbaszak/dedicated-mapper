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
         * @var null|PropertyReflection|SimpleObjectType|self $parent  
         * collection can be nested in another collection, if `null` then it is root collection
         */
        protected null|PropertyReflection|SimpleObjectType|self $parent,

        /**
         * @var ArrayObject<ClassReflection|CollectionType|SimpleObjectType|Type> $children
         */
        protected ArrayObject $children,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
    ) {}

    /**
     * @return null|PropertyReflection|SimpleObjectType|self
     */
    public function getParent(): null|PropertyReflection|SimpleObjectType|self
    {
        return $this->parent;
    }

    /**
     * @return ArrayObject<ClassReflection|CollectionType|SimpleObjectType|Type>
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
