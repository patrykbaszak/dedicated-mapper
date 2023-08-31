<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\AttributeReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use ReflectionClass;

class SimpleObjectType implements TypeInterface
{
    public function __construct(
        /** 
         * @var CollectionType|PropertyReflection $parent
         */
        protected CollectionType|PropertyReflection $parent,

        /**
         * @var Type $type of simpleObject main property
         */
        protected Type $type,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,

        /**
         * @var CollectionType|null $collection if `null`, then property is not collection
         */
        protected ?CollectionType $collection = null,

        /**
         * @var ReflectionClass|null $reflection
         */
        protected ?ReflectionClass $reflection = null,
    ) {}

    /**
     * @return CollectionType|PropertyReflection
     */
    public function getParent(): CollectionType|PropertyReflection
    {
        return $this->parent;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }

    /**
     * @return null|CollectionType
     */
    public function getCollection(): ?CollectionType
    {
        return $this->collection;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection !== null;
    }
}
