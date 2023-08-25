<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

class AttributeReflection
{
    /** 
     * @var ClassReflection|CollectionReflection|PropertyReflection $parent each attribute must have resource
     */
    protected ClassReflection|CollectionReflection|PropertyReflection $parent;

    /**
     * @var array<object{"class": string, "arguments": mixed[]}> $attributes
     */
    protected ArrayObject $attributes;
}
