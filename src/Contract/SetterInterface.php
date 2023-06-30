<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface
{
    public function createPropertySetter(Property $property): Setter;
    public function createBlueprintSetter(Blueprint $blueprint): Setter;
}
