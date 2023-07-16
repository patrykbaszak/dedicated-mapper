<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator\Symfony;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

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
