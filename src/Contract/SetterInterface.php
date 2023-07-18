<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Contract;

use PBaszak\DedicatedMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Setter;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

interface SetterInterface
{
    public function getTargetType(Blueprint $blueprint): string;

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getSetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getSetter(Property $property): Setter;
}
