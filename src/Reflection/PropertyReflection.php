<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Reflection;

use PBaszak\DedicatedMapper\Utils\getAttributes;

class PropertyReflection
{
    use getAttributes;

    public static function createFromReflection(\ReflectionProperty $reflection, ClassReflection $parent): self
    {
        $ref = new \ReflectionClass(self::class);
        /** @var PropertyReflection $instance */
        $instance = $ref->newInstanceWithoutConstructor();
        $ref->getProperty('parent')->setValue($instance, $parent);
        $ref->getProperty('name')->setValue($instance, $reflection->getName());
        $ref->getProperty('reflection')->setValue($instance, $reflection);
        $constructorParameterReflection = $parent->getReflection()->getConstructor()?->getParameters()[$reflection->getName()] ?? null;
        $ref->getProperty('reflectionParameter')->setValue($instance, $constructorParameterReflection);
        $attributes = new AttributeReflection($instance, self::getAttributesFromReflection($reflection));
        $ref->getProperty('attributes')->setValue($instance, $attributes);
        $ref->getProperty('options')->setValue($instance, new Options());

        // type

        return $instance;
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
         * @var TypeReflection $type
         */
        protected TypeReflection $type,

        /** 
         * @var ClassReflection|null $class if `null`, then property is not class
         */
        protected ?ClassReflection $class = null,

        /**
         * @var CollectionReflection|null $collection if `null`, then property is not collection
         */
        protected ?CollectionReflection $collection = null,

        /**
         * @var ?SimpleObjectReflection $simpleObject if `null`, then property is not simpleObject
         */
        protected ?SimpleObjectReflection $simpleObject = null,
        
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
    * @return TypeReflection
    */
    public function getType(): TypeReflection
    {
        return $this->type;
    }

    /**
     * @return null|ClassReflection
     */
    public function getClass(): ?ClassReflection
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->class !== null;
    }

    /**
     * @return null|CollectionReflection
     */
    public function getCollection(): ?CollectionReflection
    {
        return $this->collection;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->collection !== null;
    }

    /**
     * @return null|SimpleObjectReflection
     */
    public function getSimpleObject(): ?SimpleObjectReflection
    {
        return $this->simpleObject;
    }

    /**
     * @return bool
     */
    public function isSimpleObject(): bool
    {
        return $this->simpleObject !== null;
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