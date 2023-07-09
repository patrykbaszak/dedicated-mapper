<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator\Symfony;

use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class SymfonyValidator implements ModificatorInterface
{
    public function init(Blueprint $blueprint, string $group = null): void
    {
        // foreach ($blueprint->properties as &$property) {
        //     $targetProperty =
        // }
    }
}
