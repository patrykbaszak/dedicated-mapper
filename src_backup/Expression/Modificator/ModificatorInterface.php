<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Modificator;

use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;

interface ModificatorInterface
{
    public function init(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null): void;
}
