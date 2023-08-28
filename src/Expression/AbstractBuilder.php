<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression;

abstract class AbstractBuilder
{
    /**
     * @param null|class-string $blueprint if You need You can change input or output type.
     *                              For example: if You want to map dto to entity.
     */
    public function __construct(protected ?string $blueprint = null)
    {}

    public function getBlueprint(): ?string
    {
        return $this->blueprint;
    }
}