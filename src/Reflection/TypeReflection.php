<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use phpDocumentor\Reflection\Type;
use ReflectionType;

class TypeReflection
{
    public function __construct(
        /** 
         * @var CollectionReflection|PropertyReflection|SimpleObjectReflection $parent each type must have resource
         */
        protected CollectionReflection|PropertyReflection|SimpleObjectReflection $parent,

        /**
         * @var array<string> $types
         */
        protected array $types,

        /**
         * @var bool $nullable
         */
        protected bool $nullable,

        /**
         * @var bool $union
         */
        protected bool $union,

        /**
         * @var bool $intersection
         */
        protected bool $intersection,

        /**
         * @var bool $class
         */
        protected bool $class,

        /**
         * @var bool $collection
         */
        protected bool $collection,

        /**
         * @var bool $simpleObject
         */
        protected bool $simpleObject,

        /**
         * @var null|ReflectionType $reflectionType
         */
        protected null|ReflectionType $reflectionType = null,

        /**
         * @var null|Type $phpDocumentorReflectionType
         */
        protected null|Type $phpDocumentorReflectionType = null,
    ) {}

    /**
     * @return CollectionReflection|PropertyReflection|SimpleObjectReflection
     */
    public function getParent(): CollectionReflection|PropertyReflection|SimpleObjectReflection
    {
        return $this->parent;
    }

    /**
     * @return array<string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isUnion(): bool
    {
        return $this->union;
    }

    /**
     * @return bool
     */
    public function isIntersection(): bool
    {
        return $this->intersection;
    }

    /**
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection;
    }
    
    /**
     * @return bool
     */
    public function isSimpleObject(): bool
    {
        return $this->simpleObject;
    }

    /**
     * @return null|ReflectionType
     */
    public function getReflectionType(): null|ReflectionType
    {
        return $this->reflectionType;
    }
}