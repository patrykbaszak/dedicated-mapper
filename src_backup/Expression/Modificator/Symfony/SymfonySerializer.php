<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Modificator\Symfony;

use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;

class SymfonySerializer implements ModificatorInterface
{
    private GetterInterface $getterBuilder;
    private SetterInterface $setterBuilder;

    public function init(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null): void
    {
        $this->getterBuilder = $getterBuilder;
        $this->setterBuilder = $setterBuilder;
    }
}
