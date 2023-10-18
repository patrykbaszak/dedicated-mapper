<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\PropertyReflection;

interface TypeInterface
{
    public function toArray(): array;
    public static function supports(Type $type): bool;
    public static function create(Type $type): TypeInterface;
}
