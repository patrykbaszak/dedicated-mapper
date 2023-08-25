<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

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

        throw new \Exception('Not implemented');
    }
}
