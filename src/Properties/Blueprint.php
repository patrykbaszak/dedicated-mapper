<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

class Blueprint
{
    /** @var array<string,mixed> */
    public array $options = [];

    public function __construct(
        public \ReflectionClass $reflection,
        /** @var Property[] $properties */
        public array $properties = [],
        public bool $isCollection = false,
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
}
