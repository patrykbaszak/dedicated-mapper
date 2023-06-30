<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ArrayIterator;
use ArrayObject;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PBaszak\MessengerMapperBundle\Attribute\SimpleObject;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

class Property
{
    use Children;
    use Reflection;
    use Type;

    public const NATIVE_SIMPLE_OBJECTS = [
        ArrayIterator::class => [],
        ArrayObject::class => [],
        DateTime::class => [],
        DateTimeImmutable::class => [],
        DateTimeZone::class => [],
        DateInterval::class => [],
    ];

    public array $options = [];
    public ?Blueprint $blueprint = null;

    public function __construct(
        public readonly string $originName,
        ReflectionProperty $reflection,
        ?ReflectionParameter $constructorParameter = null,
        ?self $parent = null,
    ) {
        $this->reflection = $reflection;
        $this->constructorParameter = $constructorParameter;
        $this->setParent($parent);
    }

    public static function create(ReflectionClass $class, string $name, ?self $parent = null): self
    {
        $reflection = $class->getProperty($name);
        $constructorParameter = ($parameters = $class->getConstructor()?->getParameters()) ? $parameters[$name] ?? null : null;

        $property = new self($name, $reflection, $constructorParameter, $parent);
        $types = $property->getTypes()->types;
        $innerTypes = $property->getTypes()->innerTypes;

        /** If collection */
        if (!empty($innerTypes)) {
            if (count($innerTypes) > 1) {
                throw new \Exception('Multiple inner types are not supported yet.');
            }

            $property->blueprint = Blueprint::create($innerTypes[0], true, $property);
        }

        /** If class */
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
}
