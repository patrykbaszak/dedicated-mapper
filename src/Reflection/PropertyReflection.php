<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

class PropertyReflection
{
    /**
     * @var ClassReflection $parent each property must have parent class
     */
    protected ClassReflection $parent;

    /**
     * @var TypeReflection $type
     */
    protected TypeReflection $type;
    
    /**
     * @var AttributeReflection $attributes
     */
    protected AttributeReflection $attributes;

    /**
     * @var CollectionReflection|null $collection if `null`, then property is not collection
     */
    protected ?CollectionReflection $collection = null;
}