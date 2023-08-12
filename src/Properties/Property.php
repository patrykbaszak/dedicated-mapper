<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Properties;

use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Attribute\InitialValueCallback;
use PBaszak\DedicatedMapper\Attribute\MappingCallback;
use PBaszak\DedicatedMapper\Attribute\SimpleObject;

class Property
{
    use Children;
    use Reflection;
    use Type;

    public const NATIVE_SIMPLE_OBJECTS = [
        \ArrayIterator::class => [],
        \ArrayObject::class => [],
        \DateTime::class => [],
        \DateTimeImmutable::class => [],
        \DateTimeZone::class => [],
        \DateInterval::class => [],
    ];

    /**
     * @var int
     *          ---0 - property with simple type (int, string, array, object, boolean, float, null)
     *          ---1 - hasClass
     *          --1- - isSimpleObject
     *          -1-- - isCollection - if true, then --xx is about collection items
     *          1--- - isCollectionInsideSimpleObject
     */
    public const PROPERTY = 0; // 0000 example: int
    public const CLASS_OBJECT = 1; // 0001 example new App\Example()
    public const SIMPLE_OBJECT = 3; // 0011 example: new DateTime())
    public const PROPERTIES_COLLECTION = 4; // 0100 example: array<string>
    public const CLASS_OBJECTS_COLLECTION = 5; // 0101 example: array<App\Example>
    public const SIMPLE_OBJECTS_COLLECTION = 7; // 0111 example: array<DateTime>
    public const SIMPLE_OBJECT_COLLECTION = 12; // 1100 example: new ArrayObject(array<string>)
    public const SIMPLE_OBJECT_CLASS_OBJECT_COLLECTION = 13; // 1101 example: new ArrayObject(array<App\Example>)
    public const SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION = 15; // 1111 example: new ArrayObject(array<DateTime>)

    /**
     * Specific property for any options you need to store and use.
     *
     * @var array<string, mixed>
     */
    public array $options = [];

    /**
     * @var MappingCallback[]
     */
    public array $callbacks = [];

    /**
     * @var MappingCallback[]
     */
    public array $collectionItemCallbacks = [];

    public ?Blueprint $blueprint = null;

    public function __construct(
        public string $originName,
        \ReflectionProperty $reflection,
        \ReflectionParameter $constructorParameter = null,
        self $parent = null,
    ) {
        $this->reflection = $reflection;
        $this->constructorParameter = $constructorParameter;
        $this->setParent($parent);
        $this->applyCallbacks();
    }

    /** @param class-string $name */
    public static function create(\ReflectionClass $class, string $name, self $parent = null): self
    {
        $reflection = $class->getProperty($name);
        $constructorParameter = ($parameters = $class->getConstructor()?->getParameters()) ? $parameters[$name] ?? null : null;

        $property = new self($name, $reflection, $constructorParameter, $parent);
        $types = $property->getTypes()->types;
        $innerTypes = $property->getTypes()->innerTypes;

        /* If collection */
        if (!empty($innerTypes)) {
            /* If collection of different types */
            if (count($innerTypes) > 1) {
                throw new \Exception('Multiple inner types are not supported yet.');
            }

            /* If collection of simple types */
            foreach ($innerTypes as $innerType) {
                if ($x = class_exists($innerType, false)) {
                    break;
                }
            }

            if (!empty($applyToCollectionItems = $reflection->getAttributes(ApplyToCollectionItems::class))) {
                $applyToCollectionItems = $applyToCollectionItems[0]->newInstance();
            } else {
                $applyToCollectionItems = null;
            }

            /* If collection of simple objects */
            if (
                $x && (!array_key_exists($innerType, self::NATIVE_SIMPLE_OBJECTS)
                    && !array_key_exists(ltrim($innerType, '\\'), self::NATIVE_SIMPLE_OBJECTS)
                ) && empty($reflection->getAttributes(SimpleObject::class))
                && empty((new \ReflectionClass($innerType))->getAttributes(SimpleObject::class))
                && empty($applyToCollectionItems?->getAttributes(SimpleObject::class))
            ) {
                /* Blueprint is only for functions, not for simple objects */
                $property->blueprint = Blueprint::create($innerType, true, $property);
            }
        }

        /* If class */
        if (!empty($types)) {
            foreach ($types as $type) {
                if (
                    class_exists($type, false)
                    && !array_key_exists($type, self::NATIVE_SIMPLE_OBJECTS)
                    && !array_key_exists(ltrim($type, '\\'), self::NATIVE_SIMPLE_OBJECTS)
                    && empty($reflection->getAttributes(SimpleObject::class))
                    && empty((new \ReflectionClass($type))->getAttributes(SimpleObject::class))
                ) {
                    $property->blueprint ??= Blueprint::create($type, false, $property);
                }
            }
        }

        return $property;
    }

    public function getPropertyType(): int
    {
        $types = $this->getTypes()->types;
        $innerTypes = $this->getTypes()->innerTypes;

        if ($this->blueprint && $this->blueprint->isCollection) {
            foreach ($types as $type) {
                if (class_exists($type, false)) {
                    if (!empty($innerTypes)) {
                        foreach ($innerTypes as $innerType) {
                            if (class_exists($innerType, false)) {
                                return self::SIMPLE_OBJECT_CLASS_OBJECT_COLLECTION;
                            }
                        }
                    }

                    throw new \LogicException('This should not happen.');
                }
            }

            return self::CLASS_OBJECTS_COLLECTION;
        }

        if ($this->blueprint) {
            return self::CLASS_OBJECT;
        }

        foreach ($types as $type) {
            if (class_exists($type, false)) {
                if (!empty($innerTypes)) {
                    foreach ($innerTypes as $innerType) {
                        if (class_exists($innerType, false)) {
                            return self::SIMPLE_OBJECT_SIMPLE_OBJECTS_COLLECTION;
                        }
                    }

                    return self::SIMPLE_OBJECT_COLLECTION;
                }

                return self::SIMPLE_OBJECT;
            }
        }

        if (!empty($innerTypes)) {
            foreach ($innerTypes as $innerType) {
                if (class_exists($innerType, false)) {
                    return self::SIMPLE_OBJECTS_COLLECTION;
                }
            }

            return self::PROPERTIES_COLLECTION;
        }

        return self::PROPERTY;
    }

    public function isSimpleObject(bool $asCollectionItem = false): bool
    {
        $type = $this->getPropertyType();

        return $asCollectionItem
            ? (bool) ($type & 2)
            : (bool) ($type & 10) && 7 !== $type;
    }

    public function isCollection(): bool
    {
        return (bool) ($this->getPropertyType() & 4);
    }

    public function isCollectionInsideSimpleObject(): bool
    {
        return (bool) ($this->getPropertyType() & 8);
    }

    public function isNativeSimpleObject(bool $asCollectionItem = false): bool
    {
        return in_array($this->getClassType($asCollectionItem), array_keys(self::NATIVE_SIMPLE_OBJECTS))
            || in_array(ltrim($this->getClassType($asCollectionItem), '\\'), array_keys(self::NATIVE_SIMPLE_OBJECTS));
    }

    public function hasDedicatedInitCallback(bool $asCollectionItem = false): bool
    {
        $attr = $asCollectionItem
            ? $this->getApplyToCollectionItemsAttribute()
            : $this->reflection;

        return !empty($attr?->getAttributes(InitialValueCallback::class))
            || $this->isSimpleObject($asCollectionItem);
    }

    public function getInitialCallbackAttribute(bool $asCollectionItem = false): ?InitialValueCallback
    {
        if (!$this->hasDedicatedInitCallback($asCollectionItem)) {
            return null;
        }

        $attributes = $asCollectionItem
            ? $this->getApplyToCollectionItemsAttribute()
            : $this->reflection;
        if (!empty($attributes?->getAttributes(InitialValueCallback::class))) {
            /** @var InitialValueCallback|\ReflectionAttribute $attr */
            $attr = $attributes->getAttributes(InitialValueCallback::class)[0];
            /** @var InitialValueCallback $attr */
            $attr = $attr instanceof \ReflectionAttribute ? $attr->newInstance() : $attr;

            return $attr;
        }

        if ($this->isSimpleObject($asCollectionItem)) {
            return new InitialValueCallback(
                $this->getPropertySimpleObjectAttribute($asCollectionItem)?->getConstructorExpression(
                    $this->getClassType($asCollectionItem),
                    $asCollectionItem,
                ) ?? throw new \LogicException('This should not happen.'),
                true,
            );
        }

        throw new \LogicException('This should not happen.');
    }

    public function getApplyToCollectionItemsAttribute(): ?ApplyToCollectionItems
    {
        if (empty($attr = $this->reflection->getAttributes(ApplyToCollectionItems::class))) {
            return null;
        }

        return $attr[0]->newInstance();
    }

    public function getPropertySimpleObjectAttribute(bool $asCollectionItem = false): ?SimpleObject
    {
        if ($asCollectionItem) {
            /** @var SimpleObject[] $attributes */
            $attributes = $this->getApplyToCollectionItemsAttribute()?->getAttributes(SimpleObject::class) ?? [];
            if (empty($attributes)) {
                if ($this->isNativeSimpleObject($asCollectionItem)) {
                    return new SimpleObject();
                }

                return null;
            }

            return $attributes[0];
        }

        $attributes = $this->reflection->getAttributes(SimpleObject::class);
        if (empty($attributes)) {
            if ($this->isNativeSimpleObject($asCollectionItem)) {
                return new SimpleObject();
            }

            return null;
        }

        return $attributes[0]->newInstance();
    }

    /** @return MappingCallback[] */
    public function getPropertyMappingCallbackAttributes(bool $asCollectionItem = false): array
    {
        if ($asCollectionItem) {
            return array_filter(
                array_map(
                    fn (object $attr): ?MappingCallback => is_subclass_of($attr, MappingCallback::class) ? $attr : null,
                    $this->getApplyToCollectionItemsAttribute()?->getAttributes() ?? []
                )
            );
        }

        return array_filter(
            array_map(
                fn (\ReflectionAttribute $attr): ?MappingCallback => is_subclass_of($attr->getName(), MappingCallback::class) ? $attr->newInstance() : null,
                $this->reflection->getAttributes()
            )
        );
    }

    /**
     * @return array<MappingCallback>
     */
    public function getSortedCallbacks(bool $asCollectionItem = false): array
    {
        $this->sortCallbacksByPriority($asCollectionItem);

        return $asCollectionItem ? $this->collectionItemCallbacks : $this->callbacks;
    }

    private function applyCallbacks(): void
    {
        $this->callbacks = $this->getPropertyMappingCallbackAttributes();
        $this->collectionItemCallbacks = $this->getPropertyMappingCallbackAttributes(true);
    }

    private function sortCallbacksByPriority(bool $asCollectionItem = false): void
    {
        $callbacks = $asCollectionItem ? $this->collectionItemCallbacks : $this->callbacks;
        usort($callbacks, function (MappingCallback $a, MappingCallback $b) {
            return $b->priority <=> $a->priority;
        });

        $asCollectionItem ? $this->collectionItemCallbacks = $callbacks : $this->callbacks = $callbacks;
    }
}
