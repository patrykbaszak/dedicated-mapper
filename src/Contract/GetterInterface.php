<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Contract;

use PBaszak\DedicatedMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Getter;
use PBaszak\DedicatedMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

interface GetterInterface
{
    public function getSourceType(Blueprint $blueprint): string;

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getGetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getGetter(Property $property): Getter;
}
