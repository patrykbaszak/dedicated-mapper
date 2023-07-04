<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use PBaszak\MessengerMapperBundle\Attribute\SimpleObject;

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

    public array $options = [];
    public ?Blueprint $blueprint = null;

    public function __construct(
        public readonly string $originName,
        \ReflectionProperty $reflection,
        \ReflectionParameter $constructorParameter = null,
        self $parent = null,
    ) {
        $this->reflection = $reflection;
        $this->constructorParameter = $constructorParameter;
        $this->setParent($parent);
    }

    public static function create(\ReflectionClass $class, string $name, self $parent = null): self
    {
        $reflection = $class->getProperty($name);
        $constructorParameter = ($parameters = $class->getConstructor()?->getParameters()) ? $parameters[$name] ?? null : null;

        $property = new self($name, $reflection, $constructorParameter, $parent);
        $types = $property->getTypes()->types;
        $innerTypes = $property->getTypes()->innerTypes;

        /* If collection */
        if (!empty($innerTypes)) {
            if (count($innerTypes) > 1) {
                throw new \Exception('Multiple inner types are not supported yet.');
            }

            $property->blueprint = Blueprint::create($innerTypes[0], true, $property);
        }

        /* If class */
        if (!empty($types)) {
            foreach ($types as $type) {
                if (
                    class_exists($type, false)
                    && !array_key_exists($type, self::NATIVE_SIMPLE_OBJECTS)
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
                return self::SIMPLE_OBJECT;
            }
        }

        return self::PROPERTY;
    }
}
