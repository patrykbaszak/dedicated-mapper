<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Properties;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Attribute\SimpleObject;

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

    public const PROPERTY = 0; // 0000 example: int
    public const CLASS_OBJECT = 1; // 0001 example new App\Example()
    public const SIMPLE_OBJECT = 2; // 0010 example: new DateTime())
    public const COLLECTION = 4; // 0100 example: array<App\Example>
    public const SIMPLE_OBJECT_COLLECTION = 6; // 0110 example: new ArrayObject(array<App\Example>)
    public const SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION = 7; // 0111 example: new ArrayObject(array<DateTime>)
    public const SIMPLE_OBJECTS_COLLECTION = 13; // 1101 example: array<DateTime>

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
            if (!class_exists($innerTypes[0], false)) {
                throw new \Exception('Inner type must be a class.');
            }

            /* If collection of simple objects */
            if (
                (!array_key_exists($innerTypes[0], self::NATIVE_SIMPLE_OBJECTS)
                    && !array_key_exists(ltrim($innerTypes[0], '\\'), self::NATIVE_SIMPLE_OBJECTS)
                ) && empty($reflection->getAttributes(SimpleObject::class))
            ) {
                /* Blueprint is only for functions, not for simple objects */
                $property->blueprint = Blueprint::create($innerTypes[0], true, $property);
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
                    return self::SIMPLE_OBJECT_COLLECTION;
                }
            }

            return self::COLLECTION;
        }

        if ($this->blueprint) {
            return self::CLASS_OBJECT;
        }

        foreach ($types as $type) {
            if (class_exists($type, false)) {
                if (!empty($innerTypes)) {
                    foreach ($innerTypes as $innerType) {
                        if (class_exists($innerType, false)) {
                            return self::SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION;
                        }
                    }
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
        }

        return self::PROPERTY;
    }

    public function getPropertySimpleObjectAttribute(): ?SimpleObject
    {
        $attributes = $this->reflection->getAttributes(SimpleObject::class);
        if (empty($attributes)) {
            if (in_array($this->getClassType(), array_keys(self::NATIVE_SIMPLE_OBJECTS))) {
                return new SimpleObject();
            }

            return null;
        }

        return $attributes[0]->newInstance();
    }

    /** @return MappingCallback[] */
    public function getPropertyMappingCallbackAttributes(): array
    {
        return array_filter(
            array_map(
                fn (\ReflectionAttribute $attr) => is_subclass_of($attr, MappingCallback::class) ? $attr->newInstance() : null,
                $this->reflection->getAttributes()
            )
        );
    }

    /**
     * @return array<MappingCallback>
     */
    public function getSortedCallbacks(): array
    {
        $this->sortCallbacksByPriority();

        return $this->callbacks;
    }

    private function applyCallbacks(): void
    {
        $this->callbacks = $this->getPropertyMappingCallbackAttributes();
    }

    private function sortCallbacksByPriority(): void
    {
        usort($this->callbacks, function (MappingCallback $a, MappingCallback $b) {
            return $b->priority <=> $a->priority;
        });
    }
}
