<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

interface TypeInterface
{
    public function toArray(): array;
    // public static function supports(Type $type): bool;
    // public static function create(Type $type): TypeInterface;
}
