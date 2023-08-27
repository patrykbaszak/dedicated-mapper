<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Utils;

use ArrayObject;

trait getAttributes
{
    protected static function getAttributesFromReflection(\ReflectionClass|\ReflectionProperty $reflection): ArrayObject
    {
        $attributes = new ArrayObject();

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[] = (object) [
                'class' => $attribute->getName(),
                'arguments' => $attribute->getArguments(),
            ];
        }

        return $attributes;
    }
}