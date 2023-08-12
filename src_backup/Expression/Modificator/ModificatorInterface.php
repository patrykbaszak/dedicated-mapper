<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Modificator;

use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Properties\Blueprint;

interface ModificatorInterface
{
    public function init(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null): void;
}
