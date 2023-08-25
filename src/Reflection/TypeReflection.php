<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

class TypeReflection
{
    /** 
     * @param ClassReflection|CollectionReflection|PropertyReflection $parent each type must have resource
     */
    protected ClassReflection|CollectionReflection|PropertyReflection $parent;
}