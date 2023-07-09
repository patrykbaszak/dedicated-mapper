<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator;

use PBaszak\MessengerMapperBundle\Properties\Blueprint;

interface ModificatorInterface
{
    public function init(Blueprint $blueprint, string $group = null): void;
}
