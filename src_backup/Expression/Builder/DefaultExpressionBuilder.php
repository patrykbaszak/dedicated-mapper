<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Contract\FunctionInterface;
use PBaszak\DedicatedMapper\Contract\LoopInterface;
use PBaszak\DedicatedMapper\Expression\Function_;
use PBaszak\DedicatedMapper\Expression\Loop;

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
