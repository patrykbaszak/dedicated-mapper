<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface GetterInterface
{
    public function createPropertyGetter(Property $property): Getter;
    public function createBlueprintGetter(Blueprint $blueprint): Getter;
}
