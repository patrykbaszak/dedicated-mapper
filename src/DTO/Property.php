<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO;

use PBaszak\MessengerMapperBundle\DTO\Properties\Mapper;
use PBaszak\MessengerMapperBundle\DTO\Properties\Serializer;
use PBaszak\MessengerMapperBundle\DTO\Properties\Validator;

class Property
{
    public const SOURCE = 1;
    public const TARGET = 2;

    public ?string $isAssignedTo = null;

    /** @var Property[] */
    private array $children = [];
    private self $mirror;

    public function __construct(
        public int $origin,
        public string $name,
        public string $type,
        public bool $isNullable,
        public bool $isCollectionItem = false,
        public null|\ReflectionType $collectionType = null,
        public null|self $parent = null,
        public null|\ReflectionClass $reflectionClass = null,
        public null|\ReflectionProperty $reflection = null,
        public null|\ReflectionParameter $reflectionParameter = null,
        public null|Mapper $mapper = null,
        public null|Serializer $serializer = null,
        public null|Validator $validator = null,
    ) {
        if ($parent) {
            $parent->addChild($this);
        }
    }

    public function addChild(self $child): void
    {
        if (!in_array($child, $this->children, true)) {
            $this->children[$child->name] = $child;
            if ($child->parent !== $this) {
                $child->parent = $this;
            }
        }
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function setMirrorProperty(self $property): void
    {
        if (!isset($this->mirror)) {
            $this->mirror = $property;
            $this->mirror->setMirrorProperty($this);
        }
    }

    public function getMirrorProperty(): self
    {
        if (!isset($this->mirror)) {
            throw new \InvalidArgumentException(sprintf('Mirror property not found for property %s in class %s.', $this->name, $this->reflectionClass?->getName()));
        }

        return $this->mirror;
    }

    public function hasMirrorProperty(): bool
    {
        return isset($this->mirror);
    }

    public function isPublic(): bool
    {
        return (bool) $this->reflection?->isPublic();
    }

    public function getName(): string
    {
        if (!isset($this->mirror)) {
            return $this->name;
        }

        return $this->mirror->serializer?->serializedName?->getSerializedName()
            ?? $this->mirror->mapper?->targetProperty?->name
            ?? $this->name;
    }

    public function getPath(string $separator): string
    {
        $path = $this->name;
        $parent = $this->parent;
        while ($parent) {
            $path = $parent->name.$separator.$path;
            $parent = $parent->parent;
        }

        return $path;
    }

    public function getGetter(): string
    {
        $getterMethods = array_filter(
            [
                $this->mapper?->accessor?->getter,
                $this->getMirrorProperty()?->mapper?->accessor?->getter,
                'get'.ucfirst($this->name),
                'is'.ucfirst($this->name),
                $this->name,
            ],
            fn ($method) => $method && $this->reflectionClass?->hasMethod($method)
        );

        if (($isEmpty = empty($getterMethods)) && !$this->isPublic()) {
            throw new \InvalidArgumentException(sprintf('Getter method not found for property %s in class %s and property is not public.', $this->name, $this->reflectionClass?->getName()));
        }

        if ($isEmpty) {
            return $this->name;
        }

        return array_shift($getterMethods).'()';
    }

    public function getSetter(): string
    {
        $setterMethods = array_filter(
            [
                $this->mapper?->accessor?->setter,
                'set'.ucfirst($this->name),
                $this->name,
            ],
            fn ($method) => $method && $this->reflectionClass?->hasMethod($method)
        );

        if (($isEmpty = empty($setterMethods)) && !$this->isPublic()) {
            throw new \InvalidArgumentException(sprintf('Setter method not found for property %s in class %s and property is not public.', $this->name, $this->reflectionClass?->getName()));
        }

        if ($isEmpty) {
            return $this->name.' = %s';
        }

        return array_shift($setterMethods).'(%s)';
    }

    public function getConstructorArguments(): iterable
    {
        foreach ($this->children as $child) {
            if ($child->reflectionParameter) {
                yield $child;
            }
        }
    }

    public function getNonConstructorArgumentsProperties(): iterable
    {
        foreach ($this->children as $child) {
            if (!$child->reflectionParameter) {
                yield $child;
            }
        }
    }
}
