<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\Getter;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface GetterInterface
{
    public function getGetter(Property $property): Getter;
}
