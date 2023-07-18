<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Contract;

use PBaszak\DedicatedMapperBundle\Expression\Assets\Functions;

interface FunctionInterface
{
    public function getFunction(): Functions;
}
