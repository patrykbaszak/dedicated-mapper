<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

trait Reflection
{
    public \ReflectionProperty $reflection;
    public ?\ReflectionParameter $constructorParameter = null;
}
