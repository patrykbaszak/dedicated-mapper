<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ReflectionParameter;
use ReflectionProperty;

trait Reflection
{
    protected ReflectionProperty $reflection;
    protected ?ReflectionParameter $constructorParameter = null;
}
