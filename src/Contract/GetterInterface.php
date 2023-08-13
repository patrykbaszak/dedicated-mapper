<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Expression\Assets\FinalExpression;
use PBaszak\DedicatedMapper\Expression\Assets\Getter;
use PBaszak\DedicatedMapper\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;

interface GetterInterface
{
    public function getSourceType(Blueprint|string $blueprint): string;

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getGetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getGetter(Property $property): Getter;
}
