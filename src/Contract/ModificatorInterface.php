<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\Expression;
use PBaszak\MessengerMapperBundle\Expression\Assets\FunctionExpression;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface ModificatorInterface
{
    /**
     * @param array<string>|null $groups
     */
    public function init(Blueprint $blueprint, ?array $groups): void;

    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void;

    public function modifyBlueprintExpression(Blueprint $sourceBlueprint, Blueprint $targetBlueprint, FunctionExpression $expression): void;
}
