<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Property;

interface SetterInterface
{
    public function createSetter(Property $property): Setter;
    public function createSimpleObjectSetter(Property $property): Setter;
}
