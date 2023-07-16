<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class ExpressionBuilder
{
    public function __construct(
        protected Blueprint $blueprint,
        protected GetterInterface $getterBuilder,
        protected SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder,
        protected LoopInterface $loopBuilder,
        protected ?array $groups = null,
    ) {
    }

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function applyModificators(array $modificators): self
    {
        // todo

        return $this;
    }

    public function build()
    {
        // todo
    }
}
