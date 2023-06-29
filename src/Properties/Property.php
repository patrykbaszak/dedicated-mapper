<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ReflectionParameter;
use ReflectionProperty;

class Property
{
    use Children;
    use Reflection;
    use Type;

    public array $options = [];
    public Blueprint $blueprint;

    public function __construct(
        public readonly string $originName,
        ReflectionProperty $reflection,
        ?ReflectionParameter $constructorParameter = null,
        ?self $parent = null,
    ) {
        $this->reflection = $reflection;
        $this->constructorParameter = $constructorParameter;
        $this->setParent($parent);
    }
}
