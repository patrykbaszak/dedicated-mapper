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
            'type' => $this->type->toArray(),
            'attributes' => $this->attributes->toArray(),
            'collection' => $this->collection?->toArray(),
        ];
    }

    public static function supports(PropertyReflection $property, Type $type, int $depth): bool
    {
        if (!$type->isClass()) {
            return false;
        }

        foreach ($type->getTypes() as $t) {
            if (class_exists($t, false)) {
                if (isset(self::NATIVE_SIMPLE_OBJECTS[$t])) {
                    return true;
                }
                $reflection = new ReflectionClass($t);
                if ($reflection->getAttributes(SimpleObject::class)) {
                    return true;
                }
            }
        }
        
        $index = 0;
        $attributes = $property->getAttributes();
        do {
            $attr = $attributes->getAttributes(SimpleObject::class);
            $attributes = $attributes->getAttributes(ApplyToCollectionItems::class)[0] ?? null;
            $index++;
        } while ($index <= $depth && !empty($attributes?->getAttributes(ApplyToCollectionItems::class)));

        return !empty($attr);
    }

    public function __construct(
        /** 
         * @var CollectionType|PropertyReflection $parent
         */
        protected CollectionType|PropertyReflection $parent,

        /**
         * @var Type $type of simpleObject main property
         */
        protected Type $type,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,

        /**
         * @var CollectionType|null $collection if `null`, then property is not collection
         */
        protected ?CollectionType $collection = null,

        /**
         * @var ReflectionClass|null $reflection
         */
        protected ?ReflectionClass $reflection = null,
    ) {}

    /**
     * @return CollectionType|PropertyReflection
     */
    public function getParent(): CollectionType|PropertyReflection
    {
        return $this->parent;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }

    /**
     * @return null|CollectionType
     */
    public function getCollection(): ?CollectionType
    {
        return $this->collection;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection !== null;
    }
}
