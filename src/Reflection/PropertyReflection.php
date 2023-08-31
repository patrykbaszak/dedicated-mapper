<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use PBaszak\DedicatedMapper\Reflection\Type\CollectionType;
use PBaszak\DedicatedMapper\Reflection\Type\SimpleObjectType;
use PBaszak\DedicatedMapper\Reflection\Type\Type;

class PropertyReflection
{
    public function __construct(
        /**
         * @var ClassReflection $parent each property must have parent class
         */
        protected ClassReflection $parent,

        /**
         * @var string $name
         */
        protected string $name,
        
        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,

        /**
         * @var Options $options
         */
        protected Options $options,
        
        /**
         * @var Type $type
         */
        protected Type $type,

        /** 
         * @var ClassReflection|null $class if `null`, then property is not class
         */
        protected ?ClassReflection $class = null,

        /**
         * @var CollectionType|null $collection if `null`, then property is not collection
         */
        protected ?CollectionType $collection = null,

        /**
         * @var ?SimpleObjectType $simpleObject if `null`, then property is not simpleObject
         */
        protected ?SimpleObjectType $simpleObject = null,
        
        /**
         * @var null|\ReflectionProperty $reflection `null` is available for reversed mapping
         */
        protected null|\ReflectionProperty $reflection = null,

        /**
         * @var null|\ReflectionParameter $reflectionParameter
         */
        protected null|\ReflectionParameter $reflectionParameter = null,
    ) {}

    /**
     * @return ClassReflection
     */
    public function getParent(): ClassReflection
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName(bool $origin = false): string
    {
        return match ($origin) {
            false => $this->options->name ?? $this->name,
            true => $this->name,
        };
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }
    
    /**
    * @return Type
    */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return null|ClassReflection
     */
    public function getClass(): ?ClassReflection
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->class !== null;
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

    /**
     * @return null|SimpleObjectType
     */
    public function getSimpleObject(): ?SimpleObjectType
    {
        return $this->simpleObject;
    }

    /**
     * @return bool
     */
    public function isSimpleObject(): bool
    {
        return $this->simpleObject !== null;
    }

    /**
     * @return null|\ReflectionProperty
     */
    public function getReflection(): ?\ReflectionProperty
    {
        return $this->reflection;
    }

    /**
     * @return null|\ReflectionParameter
     */
    public function getReflectionParameter(): ?\ReflectionParameter
    {
        return $this->reflectionParameter;
    }
}