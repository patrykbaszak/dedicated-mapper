<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Modificator\Symfony;

use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Expression\Modificator\ModificatorInterface;
use PBaszak\DedicatedMapper\Properties\Blueprint;

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
