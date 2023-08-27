<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use PBaszak\DedicatedMapper\Utils\getAttributes;
use ReflectionClass;

class ClassReflection
{
    use getAttributes;

    public static function createFromReflection(\ReflectionClass $reflection, null|CollectionReflection|PropertyReflection $parent): self
    {
        $ref = new ReflectionClass(self::class);
        /** @var ClassReflection $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('reflection')->setValue($instance, $reflection);
        $ref->getProperty('parent')->setValue($instance, $parent);
        $attributes = new AttributeReflection($instance, self::getAttributesFromReflection($reflection));
        $ref->getProperty('attributes')->setValue($instance, $attributes);

        $properties = new ArrayObject();
        foreach ($reflection->getProperties() as $property) {
            // $properties[$property->getName()] = PropertyReflection::createFromReflection($property);
        }
        $ref->getProperty('properties')->setValue($instance, $properties);

        return $instance;
    }

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
