<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression;

use LogicException;

abstract class AbstractBuilder
{
    /**
     * @param null|class-string $blueprint if You need You can change input or output type.
     *                              For example: if You want to map dto to entity.
     */
    public function __construct(protected ?string $blueprint = null)
    {
        if (null !== $blueprint && !class_exists($blueprint, false)) {
            throw new LogicException("Given (`$blueprint`) blueprint class does not exists.");
        }
    }

    public function getBlueprint(): ?string
    {
        return $this->blueprint;
    }
}
