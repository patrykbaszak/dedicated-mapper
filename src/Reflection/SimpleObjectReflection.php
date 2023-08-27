<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

class SimpleObjectReflection
{
    public function __construct(
        /** 
         * @var CollectionReflection|PropertyReflection $parent
         */
        protected CollectionReflection|PropertyReflection $parent,

        /**
         * @var TypeReflection $type of simpleObject main property
         */
        protected TypeReflection $type,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,

        /**
         * @var CollectionReflection|null $collection if `null`, then property is not collection
         */
        protected ?CollectionReflection $collection = null,
    ) {}

    /**
     * @return CollectionReflection|PropertyReflection
     */
    public function getParent(): CollectionReflection|PropertyReflection
    {
        return $this->parent;
    }

    /**
     * @return TypeReflection
     */
    public function getType(): TypeReflection
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
     * @return null|CollectionReflection
     */
    public function getCollection(): ?CollectionReflection
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
