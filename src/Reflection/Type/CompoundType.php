<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use InvalidArgumentException;
use LogicException;

class CompoundType implements TypeInterface
{
    /**
     * @var array<TypeInterface> $types
     */
    protected array $types = [];

    public static function supports(Type $type): bool
    {
        return $type->isUnion();
    }

    public static function create(Type $type): TypeInterface
    {
        if (!self::supports($type)) {
            throw new LogicException('Given Type does not support compound type.');
        }

        return new self($type);
    }

    public function __construct(
        protected Type $type,
    ) {
        if (!$type->isUnion()) {
            throw new InvalidArgumentException('Given type is not a union type.');
        }

        foreach ($type->getTypes() as $type) {
            $this->types[] = TypeFactory::create($type);
        }
    }

    public function toArray(): array
    {
        return [
            'classType' => self::class,
            'type' => $this->type->toArray(),
        ];
    }
}
