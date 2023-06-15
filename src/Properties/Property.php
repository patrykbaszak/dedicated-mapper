<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ReflectionParameter;
use ReflectionProperty;

abstract class Property
{
    /** @var Property[] */
    private array $children = [];

    public function __construct(
        public string $originName,
        public ReflectionProperty $reflection,
        public ?ReflectionParameter $constructorParameter = null,
        public ?self $parent = null,
    ) {
        if ($parent) {
            $parent->addChild($this);
        }
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function addChild(self $child): void
    {
        if (!in_array($child, $this->children, true)) {
            $this->children[$child->originName] = $child;
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

    public function getAllParents(): array
    {
        $parents = [];
        $property = $this;
        while (null !== $property->parent) {
            $parents[] = $property = $property->parent;
        }

        return array_reverse($parents);
    }
}
