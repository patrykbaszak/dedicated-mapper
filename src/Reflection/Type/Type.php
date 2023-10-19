<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Attribute\ApplyToCollectionItems;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use phpDocumentor\Reflection\Type as PhpDocumentorType;
use ReflectionType;

class Type implements TypeInterface
{
    /** 
     * @var null|PropertyReflection|TypeInterface $parent
     */
    protected null|PropertyReflection|TypeInterface $parent;

    /**
     * @var array<string> $types
     */
    protected array $types;

    /**
     * @var null|TypeInterface $innerType
     */
    protected ?TypeInterface $innerType = null;

    /**
     * @var bool $nullable
     */
    protected bool $nullable = false;

    /**
     * @var bool $union
     */
    protected bool $union = false;

    /**
     * @var bool $intersection
     */
    protected bool $intersection = false;

    /**
     * @var bool $class
     */
    protected bool $class = false;

    /**
     * @var bool $collection
     */
    protected bool $collection = false;

    /**
     * @var bool $simpleObject
     */
    protected bool $simpleObject = false;

    /**
     * @var null|ReflectionType $reflectionType
     */
    protected null|ReflectionType $reflectionType = null;

    /**
     * @var null|PhpDocumentorType $phpDocumentorReflectionType
     */
    protected null|PhpDocumentorType $phpDocumentorReflectionType = null;

    public static function supports(self $type): bool
    {
        return true;
    }

    public static function create(Type $type): TypeInterface
    {
        return $type;
    }

    public function toArray(): array
    {
        return [
            'classType' => self::class,
            'types' => $this->types,
            'innerType' => $this->innerType?->toArray(),
            'nullable' => $this->nullable,
            'union' => $this->union,
            'intersection' => $this->intersection,
            'class' => $this->class,
            'collection' => $this->collection,
            'simpleObject' => $this->simpleObject,
        ];
    }

    /**
     * @return null|PropertyReflection|TypeInterface
     */
    public function getParent(): null|PropertyReflection|TypeInterface
    {
        return $this->parent;
    }

    public function getPropertyReflection(): null|PropertyReflection
    {
        $ref = $this;
        do {
            $ref = $ref->getParent();
        } while ($ref instanceof TypeInterface);

        return $ref;
    }

    /**
     * @return array<string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array<class-string>
     */
    public function getClassTypes(): array
    {
        return array_filter($this->types, fn (string $type) => class_exists($type, false));
    }

    /**
     * @return array<string>
     */
    public function getScalarTypes(): array
    {
        return array_filter($this->types, fn (string $type) => !class_exists($type, false));
    }

    /**
     * @return null|TypeInterface
     */
    public function getInnerType(): null|TypeInterface
    {
        return $this->innerType;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return bool
     */
    public function isUnion(): bool
    {
        return $this->union;
    }

    /**
     * @return bool
     */
    public function isIntersection(): bool
    {
        return $this->intersection;
    }

    /**
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection;
    }
    
    /**
     * @return bool
     */
    public function isSimpleObject(): bool
    {
        return $this->simpleObject;
    }

    /**
     * @return null|ReflectionType
     */
    public function getReflectionType(): null|ReflectionType
    {
        return $this->reflectionType;
    }

    /**
     * @return null|PhpDocumentorType
     */
    public function getPhpDocumentorReflectionType(): null|PhpDocumentorType
    {
        return $this->phpDocumentorReflectionType;
    }

    /**
     * @param class-string $class
     */
    public function hasAttribute(string $class): bool
    {
        $ref = $this;
        $depth = 0;
        do {
            $ref = $ref->getParent();
            $depth++;
        } while ($ref instanceof TypeInterface);

        if (!$ref) {
            return false;
            /** @var PropertyReflection $ref */
        }

        if ($depth > 1) {
            $attributes = $ref->getAttributes();
            $index = 1;
            do {
                $attributes = $attributes->getAttribute(ApplyToCollectionItems::class)[0] ?? null;
                $index++;
            } while ($index <= $depth && !empty($attributes?->getAttribute(ApplyToCollectionItems::class)));
            if ($attributes) {
                return $attributes->getAttribute()->hasAttribute($class);
            }
        }

        return $ref->getAttributes()->hasAttribute($class);
    }

    /**
     * @param class-string $class
     */
    public function getAttribute(string $class): ?object
    {
        $ref = $this;
        $depth = 0;
        do {
            $ref = $ref->getParent();
            $depth++;
        } while ($ref instanceof TypeInterface);

        if (!$ref) {
            return null;
            /** @var PropertyReflection $ref */
        }

        if ($depth > 1) {
            $attributes = $ref->getAttributes();
            $index = 1;
            do {
                $attributes = $attributes->getAttributes(ApplyToCollectionItems::class)[0] ?? null;
                $index++;
            } while ($index <= $depth && !empty($attributes?->getAttributes(ApplyToCollectionItems::class)));
            if ($attributes) {
                return $attributes->getAttributes()->getAttribute($class);
            }
        }

        return $ref->getAttributes()->getAttribute($class);
    }
}
