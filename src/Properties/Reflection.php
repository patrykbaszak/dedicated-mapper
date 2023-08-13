<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Properties;

trait Reflection
{
    public \ReflectionProperty $reflection;
    public ?\ReflectionParameter $constructorParameter = null;
}
