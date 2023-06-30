<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ReflectionClass;

class Blueprint
{
    public function __construct(
        public ReflectionClass $reflection,
        public array $properties = [],
        public bool $isCollection = false,
        public ?string $originVariableName = null,
        public ?string $targetVariableName = null,
    ) {}

    /**
     * @param class-string $class
     */
    public static function create(string $class, bool $isCollection, ?Property $parent = null): self
    {
        $reflection = new ReflectionClass($class);
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            $properties[$property->getName()] = Property::create($reflection, $property->getName(), $parent);
        }

        return new self($reflection, $properties, $isCollection);
    }
}
