<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use ReflectionClass;

class ClassReflection
{
    public function __construct(
        /**
         * @var null|\ReflectionClass $reflection `null` is available for reversed mapping
         *                                        (for creates php class from for example json)
         */
        protected null|\ReflectionClass $reflection = null,
    
        /**
         * @var PropertyReflection|CollectionReflection|null $parent if `null`, then it is root class
         */
        protected null|CollectionReflection|PropertyReflection $parent = null,
    
        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
        
        /** 
         * @var ArrayObject<string, PropertyReflection> $properties 
         */
        protected ArrayObject $properties,
    ) {}

    /**
     * @return null|\ReflectionClass
     */
    public function getReflection(): null|\ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return PropertyReflection|CollectionReflection|null
     */
    public function getParent(): null|CollectionReflection|PropertyReflection
    {
        return $this->parent;
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }

    /**
     * @return ArrayObject<string, PropertyReflection>
     */
    public function getProperties(): ArrayObject
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * 
     * @return PropertyReflection|null
     */ 
    public function getProperty(string $name): ?PropertyReflection
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @param string $name
     * 
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }
}
