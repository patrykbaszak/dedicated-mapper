<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\MessengerMapperBundle\Expression\Assets\Getter;
use PBaszak\MessengerMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface GetterInterface
{
    public function getSourceType(Blueprint $blueprint): string;

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression;

    public function getGetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression;

    public function getGetter(Property $property): Getter;
}
