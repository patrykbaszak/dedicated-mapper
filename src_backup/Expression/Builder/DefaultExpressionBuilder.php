<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Builder;

use PBaszak\DedicatedMapperBundle\Contract\FunctionInterface;
use PBaszak\DedicatedMapperBundle\Contract\LoopInterface;
use PBaszak\DedicatedMapperBundle\Expression\Function_;
use PBaszak\DedicatedMapperBundle\Expression\Loop;

class DefaultExpressionBuilder implements FunctionInterface, LoopInterface
{
    public function getFunction(): Function_
    {
        return new Function_(
            'function ({{originVariableType}} ${{originVariableName}}){{returnType}} {
                {{functionBody}}
                return ${{outputVariableName}};
            }'
        );
    }

    public function getLoop(): Loop
    {
        return new Loop(
            '${{outputVariableName}} = [];
            foreach ({{iterableGetter}} as $index => ${{sourceVariableName}}) {
                {{code}}
            }
            {{iterableSetter}}'
        );
    }
}
