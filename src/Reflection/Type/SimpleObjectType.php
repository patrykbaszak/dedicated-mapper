<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Attribute\SimpleObject;
use PBaszak\DedicatedMapper\Reflection\AttributeReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use PBaszak\DedicatedMapper\Utils\NativeSimpleObject;
use ReflectionClass;

class SimpleObjectType implements TypeInterface
{
    public const NATIVE_SIMPLE_OBJECTS = [
        \ArrayIterator::class => [],
        \ArrayObject::class => [],
        \DateTime::class => ['staticConstructor' => NativeSimpleObject::class.'::DateTimeConstructor'],
        \DateTimeImmutable::class => ['staticConstructor' => NativeSimpleObject::class.'::DateTimeConstructor'],
        \DateTimeZone::class => ['staticConstructor' => NativeSimpleObject::class.'::DateTimeZoneConstructor'],
        \DateInterval::class => [],
    ];

    public function toArray(): array
    {
        return [
            'classType' => self::class,
            'simpleObject' => $this->simpleObjectAttr ? [
                'class' => $this->simpleObjectAttr->class,
                'arguments' => $this->simpleObjectAttr->arguments,
                'instance' => var_export($this->simpleObjectAttr->instance, true),
            ] : null,
            'type' => $this->type->toArray(),
        ];
    }

    public static function supports(Type $type): bool
    {
        if (!$type->isClass()) {
            return false;
        }

        foreach ($type->getTypes() as $t) {
            if (class_exists($t, false)) {
                if (isset(self::NATIVE_SIMPLE_OBJECTS['\\' . ltrim($t, '\\')])) {
                    return true;
                }
                $reflection = new ReflectionClass($t);
                if (!empty($reflection->getAttributes(SimpleObject::class))) {
                    return true;
                }
            }
        }
        
        return $type->hasAttribute(SimpleObject::class);
    }

    public static function create(Type $type): static
    {
        return new static(
            $type,
            $type->getAttribute(SimpleObject::class)
        );
    }

    public function __construct(
        /**
         * @var Type $type of simpleObject main property
         */
        protected Type $type,

        /**
         * @var null|object{"class": string, "arguments": mixed[], "instance": ?object} $simpleObjectAttr
         */
        protected ?object $simpleObjectAttr,
    ) {}

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return null|object{"class": string, "arguments": mixed[], "instance": ?object}
     */
    public function getAttribute(): ?object
    {
        return $this->simpleObjectAttr;
    }
}
