<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

class CollectionType implements TypeInterface
{
    public function toArray(): array
    {
        return [
            'classType' => self::class,
            'type' => $this->type->toArray(), 
        ];
    }

    public static function supports(Type $type): bool
    {
        return $type->isCollection();
    }

    public static function create(Type $type): static
    {
        return new self($type);
    }

    public function __construct(
        /**
         * @var Type $type
         */
        protected Type $type,
    ) {
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }
}
