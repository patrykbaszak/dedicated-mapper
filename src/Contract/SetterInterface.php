<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\Setter;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface
{
    public function getSetter(Property $property): Setter;
}
