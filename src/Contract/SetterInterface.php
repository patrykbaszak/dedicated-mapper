<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\MessengerMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Assets\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface
{
    public function getTargetType(Blueprint $blueprint): string;

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getSetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getSetter(Property $property): Setter;
}
