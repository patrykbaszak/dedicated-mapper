<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface GetterInterface
{
    public function createGetter(Property $property): Getter;

    public function createSimpleObjectGetter(Property $property): Getter;
}
