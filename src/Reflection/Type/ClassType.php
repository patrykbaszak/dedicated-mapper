<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\ClassReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;

class ClassType implements TypeInterface
{
    public static array $classTypes = [];
    
    /**
     * @param class-string $class
     */
    public static function isClassTypeExists(string $class): bool
    {
        return isset(self::$classTypes[$class]);
    }

    public static function storeClassType(ClassType $instance): void
    {
        $class = $instance->getReflection()->getReflection()?->getName();
        if (!class_exists($class, false)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exists', $class));
        }
        self::$classTypes[$class] = $instance;
    }

    public static function supports(Type $type): bool
    {
        foreach ($type->getTypes() as $t) {
            if (class_exists($t, false)) {
                return true;
            }
        }
    }

    public static function create(Type $type): TypeInterface
    {
        
    }

    public function toArray(): array
    {
        return $this->reflection?->toArray();
    }

    public function __construct(
        /**
         * @var ClassReflection $reflection
         */
        protected ClassReflection $reflection,
    
        /**
         * @var PropertyReflection|TypeInterface|null $parent if `null`, then it is root class
         */
        protected null|PropertyReflection|TypeInterface $parent = null,
    ) {}

    /**
     * @return null|ClassReflection
     */
    public function getReflection(): null|ClassReflection
    {
        return $this->reflection;
    }

    /**
     * @return PropertyReflection|TypeInterface|null
     */
    public function getParent(): null|PropertyReflection|TypeInterface
    {
        return $this->parent;
    }
}
