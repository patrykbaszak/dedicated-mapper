<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

class Blueprint
{
    public function __construct(
        public \ReflectionClass $reflection,
        /** @var Property[] $properties */
        public array $properties = [],
        public bool $isCollection = false,
        public ?string $originVariableName = null,
        public ?string $targetVariableName = null,
    ) {
    }

    /**
     * @param class-string $class
     */
    public static function create(string $class, bool $isCollection = false, Property $parent = null): self
    {
        $reflection = new \ReflectionClass($class);
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            /** @var class-string $class */
            $class = $property->getName();
            $properties[$property->getName()] = Property::create($reflection, $class, $parent);
        }

        return new self($reflection, $properties, $isCollection);
    }
}
