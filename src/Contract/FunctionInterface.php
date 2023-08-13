<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Expression\Assets\Functions;

interface FunctionInterface
{
    public function getFunction(): Functions;
}
