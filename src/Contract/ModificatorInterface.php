<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Expression\Assets\Expression;
use PBaszak\DedicatedMapper\Expression\Assets\FunctionExpression;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;

interface ModificatorInterface
{
    /**
     * @param array<string>|null $groups
     */
    public function init(Blueprint $blueprint, ?array $groups): void;

    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void;

    public function modifyBlueprintExpression(Blueprint $sourceBlueprint, Blueprint $targetBlueprint, FunctionExpression $expression): void;
}
