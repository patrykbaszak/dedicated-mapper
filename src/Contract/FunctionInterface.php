<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Assets\Functions;

interface FunctionInterface
{
    public function getFunction(): Functions;
}
