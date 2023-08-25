<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

class ClassReflection
{
    /**
     * @var PropertyReflection|CollectionReflection|null $parent if `null`, then it is root class
     */
    protected null|CollectionReflection|PropertyReflection $parent = null;

    /**
     * @var TypeReflection $type
     */
    protected TypeReflection $type;

    /**
     * @var AttributeReflection $attributes
     */
    protected AttributeReflection $attributes;
    
    /** 
     * @var PropertyReflection[] $properties 
     */
    protected ArrayObject $properties;
}