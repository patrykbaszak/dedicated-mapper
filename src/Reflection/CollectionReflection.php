<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

class CollectionReflection
{
    public function __construct(
        /** 
         * @var null|PropertyReflection|SimpleObjectReflection|self $parent  
         * collection can be nested in another collection, if `null` then it is root collection
         */
        protected null|PropertyReflection|SimpleObjectReflection|self $parent,

        /**
         * @var ArrayObject<ClassReflection|CollectionReflection|SimpleObjectReflection|TypeReflection> $children
         */
        protected ArrayObject $children,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
    ) {}

    /**
     * @return null|PropertyReflection|SimpleObjectReflection|self
     */
    public function getParent(): null|PropertyReflection|SimpleObjectReflection|self
    {
        return $this->parent;
    }

    /**
     * @return ArrayObject<ClassReflection|CollectionReflection|SimpleObjectReflection|TypeReflection>
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
