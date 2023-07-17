<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\Expression;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface ModificatorInterface
{
    public function modifyPropertyExpression(Property $sourceProperty, Property $targetProperty, Expression $expression): void;
}
