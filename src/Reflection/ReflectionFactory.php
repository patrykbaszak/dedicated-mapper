<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Reflection\Type\ClassType;
use PBaszak\DedicatedMapper\Reflection\Type\TypeInterface;
use PBaszak\DedicatedMapper\Utils\GetParametersFromObject;
use ReflectionClass;

class ReflectionFactory
{
    use GetParametersFromObject;

    public function createClassReflection(string $class, null|ClassType $parent): ClassReflection
    {
        $reflection = new ReflectionClass($class);
        $ref = new ReflectionClass(ClassReflection::class);
        /** @var ClassReflection $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('reflection')->setValue($instance, $reflection);
        $ref->getProperty('parent')->setValue($instance, $parent);
        $attributes = new AttributeReflection($instance, $this->getAttributesFromReflection($reflection));
        $ref->getProperty('attributes')->setValue($instance, $attributes);

        $properties = new ArrayObject();
        foreach ($reflection->getProperties() as $property) {
            $properties[$property->getName()] = $this->createPropertyReflection($property, $instance);
        }
        $ref->getProperty('properties')->setValue($instance, $properties);
        $ref->getProperty('options')->setValue($instance, new Options());

        return $instance;
    }

    public function createPropertyReflection(\ReflectionProperty $reflection, ClassReflection $parent): PropertyReflection
    {
        $ref = new \ReflectionClass(PropertyReflection::class);
        /** @var PropertyReflection $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('parent')->setValue($instance, $parent);
        $ref->getProperty('name')->setValue($instance, $reflection->getName());
        $ref->getProperty('reflection')->setValue($instance, $reflection);
        $constructorParameterReflection = $parent->getReflection()->getConstructor()?->getParameters()[$reflection->getName()] ?? null;
        $ref->getProperty('reflectionParameter')->setValue($instance, $constructorParameterReflection);
        $attributes = new AttributeReflection($instance, $this->getAttributesFromReflection($reflection));
        $ref->getProperty('attributes')->setValue($instance, $attributes);
        $ref->getProperty('options')->setValue($instance, new Options());

        // type

        return $instance;
    }

    public function createAttributeReflection(
        \ReflectionClass|\ReflectionProperty|ApplyToCollectionItems $source, 
        ClassReflection|PropertyReflection|TypeInterface $parent
    ): AttributeReflection {
        $attributes = match(true) {
            $source instanceof \ReflectionClass => $this->getAttributesFromReflection($source),
            $source instanceof \ReflectionProperty => $this->getAttributesFromReflection($source),
            $source instanceof ApplyToCollectionItems => $this->getAttributesFromApplyToCollectionItemsAttribute($source),
        };

        return new AttributeReflection($parent, $attributes);
    }

    protected function getAttributesFromReflection(\ReflectionClass|\ReflectionProperty $reflection): ArrayObject
    {
        $attributes = new ArrayObject();

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[] = (object) [
                'class' => $attribute->getName(),
                'arguments' => $attribute->getArguments(),
                'instance' => $attribute->newInstance(),
            ];
        }

        return $attributes;
    }

    protected function getAttributesFromApplyToCollectionItemsAttribute(ApplyToCollectionItems $attribute): ArrayObject
    {
        $attributes = new ArrayObject();

        foreach ($attribute->getAttributes() as $attribute) {
            $attributes[] = (object) [
                'class' => get_class($attribute),
                'arguments' => $this->getParametersFromObject($attribute),
                'instance' => $attribute,
            ];
        }

        return $attributes;
    }
}
