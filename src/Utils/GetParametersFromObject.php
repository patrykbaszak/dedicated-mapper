<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Utils;

use ReflectionClass;

trait GetParametersFromObject
{
    /**
     * @param object $object
     * 
     * @return array<string, mixed>
     */
    protected function getParametersFromObject(object $object): array
    {
        $ref = new ReflectionClass($object);
        if (! $ref->isInstantiable()) {
            return (object) [];
        }

        $parameters = [];
        $constructor = $ref->getConstructor();
        $params = $constructor->getParameters();

        foreach ($params as $param) {
            $name = $param->getName();
            $parameters[$name] = $ref->getProperty($name)->getValue($object);
        }

        return $parameters;
    }
}
