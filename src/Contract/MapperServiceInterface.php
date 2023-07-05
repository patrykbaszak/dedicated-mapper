<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Builder\DefaultExpressionBuilder;

interface MapperServiceInterface
{
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = new DefaultExpressionBuilder(),
        LoopInterface $loopBuilder = new DefaultExpressionBuilder(),
        bool $isCollection = false,
    ): mixed;
}
