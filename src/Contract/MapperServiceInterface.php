<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

interface MapperServiceInterface
{
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = null,
        LoopInterface $loopBuilder = null,
        bool $isCollection = false,
    ): mixed;
}
