<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Expression\Assets\FinalExpression;
use PBaszak\DedicatedMapper\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapper\Expression\Assets\Setter;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;

interface SetterInterface
{
    public function getTargetType(Blueprint|string $blueprint): string;

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getSetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getSetter(Property $property): Setter;
}
