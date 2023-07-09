<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;

interface MapperServiceInterface
{
    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     * @param bool                   $throwException If true, exception will be thrown when not found property in data and no default value is set
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface $getterBuilder,
        SetterInterface $setterBuilder,
        FunctionInterface $functionBuilder = null,
        LoopInterface $loopBuilder = null,
        bool $throwException = false,
        bool $isCollection = false,
        array $modificators = [],
        string $group = null,
    ): mixed;
}
