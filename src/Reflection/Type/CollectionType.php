<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use ArrayObject;
use PBaszak\DedicatedMapper\Reflection\AttributeReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;
use PBaszak\DedicatedMapper\Utils\ToArrayTrait;

class CollectionType implements TypeInterface
{
    public function toArray(): array
    {
        return [
            'attributes' => $this->attributes->toArray(),
            'classType' => self::class,
            'type' => $this->type->toArray(), 
        ];
    }

    public static function supports(Type $type): bool
    {
        return $type->isCollection();
    }

    public static function create(Type $type): TypeInterface
    {
        
    }

    public function __construct(
        /** 
         * @var null|PropertyReflection|TypeInterface $parent  
         * collection can be nested in another collection, if `null` then it is root collection
         */
        protected null|PropertyReflection|TypeInterface $parent,

        /**
         * @var Type $type
         */
        protected Type $type,

        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,
    ) {
    }

    /**
     * @return null|PropertyReflection|TypeInterface
     */
    public function getParent(): null|PropertyReflection|TypeInterface
    {
        return $this->parent;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }
}
