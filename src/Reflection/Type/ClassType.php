<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection\Type;

use PBaszak\DedicatedMapper\Reflection\ClassReflection;
use PBaszak\DedicatedMapper\Reflection\PropertyReflection;

class ClassType implements TypeInterface
{
    public function __construct(
        /**
         * @var null|ClassReflection $reflection if `null`, then it is root class
         */
        protected null|ClassReflection $reflection = null,
    
        /**
         * @var PropertyReflection|TypeInterface|null $parent if `null`, then it is root class
         */
        protected null|PropertyReflection|TypeInterface $parent = null,
    ) {}

    /**
     * @return null|ClassReflection
     */
    public function getReflection(): null|ClassReflection
    {
        return $this->reflection;
    }

    /**
     * @return PropertyReflection|TypeInterface|null
     */
    public function getParent(): null|PropertyReflection|TypeInterface
    {
        return $this->parent;
    }
}
