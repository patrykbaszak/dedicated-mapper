<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use ArrayObject;

class CollectionReflection
{
    /** 
     * @var null|PropertyReflection|self $parent collection can be nested in another collection,
     *                    if `null` then it is root collection
     */
    protected null|PropertyReflection|self $parent;

    /**
     * @var ClassReflection|PropertyReflection|CollectionReflection|TypeReflection $children
     */
    protected ArrayObject $children;
    
    /**
     * @var AttributeReflection $attributes
     */
    protected AttributeReflection $attributes;
}