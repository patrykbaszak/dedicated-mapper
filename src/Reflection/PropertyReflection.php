<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use PBaszak\DedicatedMapper\Reflection\Type\TypeInterface;

class PropertyReflection implements ReflectionInterface
{
    public function toArray(): array
    {
        return [
            'attributes' => $this->attributes->toArray(),
            'name' => $this->name,
            'options' => $this->options->toArray(),
            'type' => $this->type->toArray(),
        ];
    }

    public function __construct(
        /**
         * @var ClassReflection $parent each property must have parent class
         */
        protected ClassReflection $parent,

        /**
         * @var string $name
         */
        protected string $name,
        
        /**
         * @var AttributeReflection $attributes
         */
        protected AttributeReflection $attributes,

        /**
         * @var Options $options
         */
        protected Options $options,

        /**
         * @var TypeInterface $type
         */
        protected TypeInterface $type,

        /**
         * @var null|\ReflectionProperty $reflection `null` is available for reversed mapping
         */
        protected null|\ReflectionProperty $reflection = null,

        /**
         * @var null|\ReflectionParameter $reflectionParameter
         */
        protected null|\ReflectionParameter $reflectionParameter = null,
    ) {}

    /**
     * @return ClassReflection
     */
    public function getParent(): ClassReflection
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName(bool $origin = false): string
    {
        return match ($origin) {
            false => $this->options->name ?? $this->name,
            true => $this->name,
        };
    }

    /**
     * @return AttributeReflection
     */
    public function getAttributes(): AttributeReflection
    {
        return $this->attributes;
    }

    /**
     * @return Options
     */
    public function getOptions(): Options
    {
        return $this->options;
    }
    
    /**
    * @return TypeInterface
    */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @return null|\ReflectionProperty
     */
    public function getReflection(): ?\ReflectionProperty
    {
        return $this->reflection;
    }

    /**
     * @return null|\ReflectionParameter
     */
    public function getReflectionParameter(): ?\ReflectionParameter
    {
        return $this->reflectionParameter;
    }
}
