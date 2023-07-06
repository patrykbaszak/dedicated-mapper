<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface extends AbstractExpressionInterface
{
    /**
     * @param string $initialExpressionId - unique id of initial expression because getter and setter have to know about each other
     */
    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression;

    public function createSetter(Property $property): Setter;

    public function createSimpleObjectSetter(Property $property): Setter;

    public function getOutputType(Blueprint $blueprint): ?string;
}
