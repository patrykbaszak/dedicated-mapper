<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Function_;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

interface FunctionInterface
{
    public function getFunction(): Function_;
}
