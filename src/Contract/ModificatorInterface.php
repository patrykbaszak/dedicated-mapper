<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Contract;

use PBaszak\DedicatedMapperBundle\Expression\Assets\Expression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

interface ModificatorInterface
{
    /**
     * @param array<string>|null $groups
     */
    public function init(Blueprint $blueprint, ?array $groups): void;

    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void;

    public function modifyBlueprintExpression(Blueprint $sourceBlueprint, Blueprint $targetBlueprint, FunctionExpression $expression): void;
}
