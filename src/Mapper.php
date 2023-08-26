<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Contract\MapperInterface;
use PBaszak\DedicatedMapper\Expression\GetterBuilderInterface;
use PBaszak\DedicatedMapper\Expression\AbstractBuilder;
use PBaszak\DedicatedMapper\Expression\SetterBuilderInterface;

class Mapper implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function map(
        array|object $data, 
        string $blueprint, 
        GetterBuilderInterface&AbstractBuilder $getterBuilder, 
        SetterBuilderInterface&AbstractBuilder $setterBuilder, 
        ?Context $context = null
    ): array|object {
        throw new \Exception('Not implemented yet');
    }
}
