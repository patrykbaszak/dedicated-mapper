<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Properties;

class Blueprint
{
    /** @var array<string,mixed> */
    public array $options = [];

    /** @param Property[] $properties */
    public function __construct(
        public \ReflectionClass $reflection,
        public array $properties = [],
        public bool $isCollection = false,
    ) {
    }

    /**
     * @param class-string $class
     */
    public static function create(string $class, bool $isCollection = false, ?Property $parent = null): self
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

    public function getProperty(string $name, bool $throwException = false): ?Property
    {
        foreach ($this->properties as $property) {
            if ($property->originName === $name) {
                return $property;
            }
        }

        if ($throwException) {
            throw new \InvalidArgumentException(sprintf('Property %s not found in %s.', $name, $this->reflection->getName()));
        }

        return null;
    }

    public function deleteProperty(string $name): void
    {
        foreach ($this->properties as $index => $property) {
            if ($property->originName === $name) {
                unset($this->properties[$index]);
            }
        }
    }

    /**
     * Includes all properties from nested blueprints.
     *
     * @return Property[]
     */
    public function getAllProperties(): array
    {
        $properties = $this->properties;
        foreach ($this->properties as $property) {
            if ($property->blueprint) {
                $properties = array_merge($properties, $property->blueprint->getAllProperties());
            }
        }

        return $properties;
    }
}
