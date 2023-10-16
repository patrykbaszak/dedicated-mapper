<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Context;
use PBaszak\DedicatedMapper\Expression\AbstractBuilder;
use PBaszak\DedicatedMapper\Expression\GetterBuilderInterface;
use PBaszak\DedicatedMapper\Expression\SetterBuilderInterface;
use PBaszak\DedicatedMapper\Modificator\ModificatorInterface;

interface MapperInterface
{
    /**
     * map() function creates mapper (function or class) and use it to map data based on blueprint.
     *
     * @param class-string $blueprint
     */
    public function map(
        array|object $data,
        string $blueprint,
        GetterBuilderInterface&AbstractBuilder $getterBuilder,
        SetterBuilderInterface&AbstractBuilder $setterBuilder,
        ?Context $context = null,
    ): array|object;
}
