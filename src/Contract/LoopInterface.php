<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Loop;

interface LoopInterface extends AbstractExpressionInterface
{
    public function getLoop(): Loop;
}
