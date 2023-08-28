<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;
use ReflectionClass;

class ReflectionFactory
{
    /**
     * @param class-string $class
     * @throws LogicException if class does not exist
     */
    public function createReflectionFromPhpClass(string $class, bool $asCollection = false): ClassReflection|CollectionReflection
    {
        if (!class_exists($class, false)) {
            throw new \LogicException('Class does not exist');
        }

        return match ($asCollection) {
            true => $this->createCollectionReflection(
                null,
                new ArrayObject([
                    $this->createClassReflection($class, null),
                ]),
                new ArrayObject(),
            ),
            false => $this->createClassReflection($class, null),
        };
    }

    protected function createClassReflection(string $class, null|CollectionReflection|PropertyReflection $parent): ClassReflection
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

    protected function createCollectionReflection(null|PropertyReflection|SimpleObjectReflection|CollectionReflection $parent, ArrayObject $children, ArrayObject $attributes): CollectionReflection
    {
        $ref = new \ReflectionClass(CollectionReflection::class);
        /** @var CollectionReflection $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('parent')->setValue($instance, $parent);
        $ref->getProperty('children')->setValue($instance, $children);
        $ref->getProperty('attributes')->setValue($instance, new AttributeReflection($instance, $attributes));

        return $instance;
    }

    protected function createSimpleObjectReflection(CollectionReflection|PropertyReflection $parent, ReflectionClass $reflection, ArrayObject $attributes): SimpleObjectReflection
    {
        $ref = new \ReflectionClass(SimpleObjectReflection::class);
        /** @var SimpleObjectReflection $instance */
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
