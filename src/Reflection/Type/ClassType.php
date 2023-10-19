<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use LogicException;
use PBaszak\DedicatedMapper\Reflection\ClassReflection;
use PBaszak\DedicatedMapper\Reflection\ReflectionFactory;

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
        $classTypes = $type->getClassTypes();

        return count($classTypes) === 1;
    }

    public static function create(Type $type): self
    {
        if (!self::supports($type)) {
            throw new LogicException('Given Type does not support class type.');
        }

        $class = $type->getClassTypes()[0];
        $ref = new \ReflectionClass(self::class);
        /** @var ClassType $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('type')->setValue($instance, $type);
        $reflection = (new ReflectionFactory())->createClassReflection($class, $instance);
        $ref->getProperty('reflection')->setValue($instance, $reflection);

        self::storeClassType($instance);

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'classType' => self::class,
            'reflection' => $this->reflection?->toArray(),
            'type' => $this->type->toArray(),
        ];
    }

    public function __construct(
        /**
         * @var ClassReflection $reflection
         */
        protected ClassReflection $reflection,

        /**
         * @var Type $type
         */
        protected Type $type,
    ) {}

    /**
     * @return null|ClassReflection
     */
    public function getReflection(): null|ClassReflection
    {
        return $this->reflection;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }
}
