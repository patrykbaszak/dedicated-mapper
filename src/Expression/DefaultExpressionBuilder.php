<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;

class DefaultExpressionBuilder implements FunctionInterface, LoopInterface
{
    public function getFunction(): Function_
    {
        return new Function_(
            'function (mixed ${{originVariableName}}) {{useStatements}} {
                {{functionBody}}
                return ${{outputVariableName}};
            }'
        );
    }

    public function getLoop(): Loop
    {
        return new Loop(
            '${{outputVariableName}} = [];
            foreach ({{iterableGetter}} as ${{sourceVariableName}}) {
                {{code}}
            }
            {{iterableSetter}}'
        );
    }
}
