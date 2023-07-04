<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface
{
    /**
     * @param string $initialExpressionId - unique id of initial expression because getter and setter have to know about each other
     */
    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression;

    public function createSetter(Property $property): Setter;

    public function createSimpleObjectSetter(Property $property): Setter;
}
