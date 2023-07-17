<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Expression\Assets\Functions;

class FunctionExpressionBuilder implements LoopInterface
{
    public function getLoop()
    {
        return 
            [
                'basic' => "",
            ]
        ;
    }
}
