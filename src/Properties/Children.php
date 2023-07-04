<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

trait Children
{
    /** @var Property[] */
    protected array $children = [];
    protected ?Property $parent = null;

    public function setParent(?Property $parent): void
    {
        if ($parent) {
            $parent->addChild($this);
            $this->parent = $parent;
        }
    }

    public function getParent(): ?Property
    {
        return $this->parent;
    }

    public function getAllParents(int $depth = null): array
    {
        $parents = [];
        $property = $this;
        while (null !== $property->parent) {
            $parents[] = $property = $property->parent;
        }

        if (null !== $depth) {
            $parents = array_slice($parents, 0, $depth);
        }

        return array_reverse($parents);
    }

    public function addChild(Property $child): void
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

    public function getChildren(): array
    {
        return $this->children;
    }
}
