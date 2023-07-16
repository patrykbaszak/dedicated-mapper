<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

interface ModificatorInterface
{
    public function init(Blueprint $blueprint, GetterInterface $getterBuilder, SetterInterface $setterBuilder, string $group = null): void;
}
