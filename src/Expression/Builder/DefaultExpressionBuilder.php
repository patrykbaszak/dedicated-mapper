<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Expression\Function_;
use PBaszak\MessengerMapperBundle\Expression\Loop;

class DefaultExpressionBuilder implements FunctionInterface, LoopInterface
{
    public function getFunction(): Function_
    {
        return new Function_(
            'function ({{originVariableType}} ${{originVariableName}}){{useStatements}}{{returnType}} {
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
