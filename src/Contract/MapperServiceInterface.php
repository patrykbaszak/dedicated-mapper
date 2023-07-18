<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Builder\AbstractBuilder;

interface MapperServiceInterface
{
    /**
     * @param class-string           $blueprint
     * @param ModificatorInterface[] $modificators
     * @param bool                   $throwExceptionOnMissingProperty If true, exception will be thrown when not found property in data and no default value is set
     * @param array<string>|null     $groups
     */
    public function map(
        mixed $data,
        string $blueprint,
        GetterInterface&AbstractBuilder $getterBuilder,
        SetterInterface&AbstractBuilder $setterBuilder,
        FunctionInterface $functionBuilder = null,
        bool $throwExceptionOnMissingProperty = false,
        bool $isCollection = false,
        array $modificators = [],
        array $groups = null,
    ): mixed;
}
