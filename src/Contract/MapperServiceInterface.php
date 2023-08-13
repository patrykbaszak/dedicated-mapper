<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Contract;

use PBaszak\DedicatedMapper\Expression\Builder\AbstractBuilder;

interface MapperServiceInterface
{
    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     * @param bool                   $throwExceptionOnMissingProperty if `false` then before each property assignment
     *                                                                there will be a condition checking whether it exists in the source data
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface&AbstractBuilder $getterBuilder,
        SetterInterface&AbstractBuilder $setterBuilder,
        FunctionInterface $functionBuilder = null,
        bool $throwExceptionOnMissingProperty = false,
        bool $isCollection = false,
        array $modificators = []
    ): mixed;
}
