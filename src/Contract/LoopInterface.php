<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Loop;

interface LoopInterface
{
    public function createLoop(): Loop;
}
