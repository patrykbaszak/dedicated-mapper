<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use PBaszak\DedicatedMapper\Reflection\Type\ClassType;
use ReflectionClass;

class ClassReflection
{
    public function __construct(
        /**
         * @var null|\ReflectionClass $reflection `null` is available for reversed mapping
         *                                        (for creates php class from for example json)
         */
        protected null|\ReflectionClass $reflection,
    
        /**
         * @var ClassType $parent
         */
        protected ClassType $parent,
    
        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
        
        /** 
         * @var ArrayObject<string, PropertyReflection> $properties 
         */
        protected ArrayObject $properties,
        
        /**
         * @var Options $options
         */
        protected Options $options,
    ) {}

    /**
     * @return null|\ReflectionClass
     */
    public function getReflection(): null|\ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return ClassType
     */
    public function getParent(): ClassType
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

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }
}
