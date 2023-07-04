<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

trait Reflection
{
    protected \ReflectionProperty $reflection;
    protected ?\ReflectionParameter $constructorParameter = null;
}
