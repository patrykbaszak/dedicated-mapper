<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\MessengerMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

abstract class AbstractBuilder
{
    /**
     * @param class-string $blueprint
     */
    public function __construct(
        protected ?string $blueprint = null,
    ) {
    }

    public function getBlueprint(bool $isCollection = false): ?Blueprint
    {
        return $this->blueprint ? Blueprint::create($this->blueprint, $isCollection) : null;
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'mixed';
    }

    public function getTargetType(Blueprint $blueprint): string
    {
        return 'mixed';
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression('');
    }

    public function getGetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression
    {
        return new FinalExpression('');
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression('');
    }

    public function getSetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression
    {
        return new FinalExpression('');
    }
}
