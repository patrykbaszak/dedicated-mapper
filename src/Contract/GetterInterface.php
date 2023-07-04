<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface GetterInterface
{
    /**
     * @param string $initialExpressionId - unique id of initial expression because getter and setter have to know about each other
     */
    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression;

    public function createGetter(Property $property): Getter;

    public function createSimpleObjectGetter(Property $property): Getter;
}
