<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use PBaszak\DedicatedMapper\Reflection\Type\CollectionType;
use PBaszak\DedicatedMapper\Reflection\Type\SimpleObjectType;
use ReflectionClass;

class ReflectionFactory
{
    /**
     * @param class-string $class
     * @throws LogicException if class does not exist
     */
    public function createReflectionFromPhpClass(string $class, bool $asCollection = false): ClassReflection|CollectionType
    {
        if (!class_exists($class, false)) {
            throw new \LogicException('Class does not exist');
        }

        return match ($asCollection) {
            true => $this->createCollectionType(
                null,
                new ArrayObject([
                    $this->createClassReflection($class, null),
                ]),
                new ArrayObject(),
            ),
            false => $this->createClassReflection($class, null),
        };
    }

    protected function createClassReflection(string $class, null|CollectionType|PropertyReflection $parent): ClassReflection
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

        return $instance;
    }

    protected function createPropertyReflection(\ReflectionProperty $reflection, ClassReflection $parent): PropertyReflection
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

    protected function createCollectionType(null|PropertyReflection|SimpleObjectType|CollectionType $parent, ArrayObject $children, ArrayObject $attributes): CollectionType
    {
        $ref = new \ReflectionClass(CollectionType::class);
        /** @var CollectionType $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('parent')->setValue($instance, $parent);
        $ref->getProperty('children')->setValue($instance, $children);
        $ref->getProperty('attributes')->setValue($instance, new AttributeReflection($instance, $attributes));

        return $instance;
    }

    protected function createSimpleObjectType(CollectionType|PropertyReflection $parent, ReflectionClass $reflection, ArrayObject $attributes): SimpleObjectType
    {
        $ref = new \ReflectionClass(SimpleObjectType::class);
        /** @var SimpleObjectType $instance */
        $instance = $ref->newInstanceWithoutConstructor();

        return $instance;
    }

    protected function getAttributesFromReflection(\ReflectionClass|\ReflectionProperty $reflection): ArrayObject
    {
        $attributes = new ArrayObject();

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[] = (object) [
                'class' => $attribute->getName(),
                'arguments' => $attribute->getArguments(),
            ];
        }

        return $attributes;
    }
}
