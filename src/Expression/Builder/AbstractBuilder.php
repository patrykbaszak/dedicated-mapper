<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Properties\Blueprint;

abstract class AbstractBuilder
{
    /** 
     * @param class-string $blueprint
     */
    public function __construct(
        protected ?string $blueprint = null,
    ) {}

    public function getBlueprint(bool $isCollection = false): ?Blueprint
    {
        return $this->blueprint ? Blueprint::create($this->blueprint, $isCollection) : null;
    }
} 
