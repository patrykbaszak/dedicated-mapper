<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use phpDocumentor\Reflection\Type as PhpDocumentorType;
use ReflectionType;

class Type implements TypeInterface
{
    public function __construct(
        /** 
         * @var CollectionType|PropertyReflection|SimpleObjectType $parent each type must have resource
         */
        protected CollectionType|PropertyReflection|SimpleObjectType $parent,

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
         * @var null|PhpDocumentorType $phpDocumentorReflectionType
         */
        protected null|PhpDocumentorType $phpDocumentorReflectionType = null,
    ) {}

    /**
     * @return CollectionType|PropertyReflection|SimpleObjectType
     */
    public function getParent(): CollectionType|PropertyReflection|SimpleObjectType
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

    /**
     * @return null|PhpDocumentorType
     */
    public function getPhpDocumentorReflectionType(): null|PhpDocumentorType
    {
        return $this->phpDocumentorReflectionType;
    }
}