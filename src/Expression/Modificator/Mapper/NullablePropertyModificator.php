<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator\Mapper;

use PBaszak\MessengerMapperBundle\Contract\AbstractExpressionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

class NullablePropertyModificator implements ModificatorInterface, SetterInterface, GetterInterface
{
    private AbstractExpressionInterface|SetterInterface|GetterInterface $expressionBuilder;

    public function getModificators(): array
    {
        return $this->expressionBuilder->getModificators();
    }

    public function getPriority(): int
    {
        return 9999;
    }

    public function setBuilder(AbstractExpressionInterface $builder): void
    {
        $this->expressionBuilder = $builder;
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return $this->expressionBuilder->getSetterInitialExpression($blueprint, $initialExpressionId);
    }

    public function createSetter(Property $property): Setter
    {
        return $this->expressionBuilder->createSetter($property);
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return $this->expressionBuilder->createSimpleObjectSetter($property);
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return $this->expressionBuilder->getGetterInitialExpression($blueprint, $initialExpressionId);
    }

    public function createGetter(Property $property): Getter
    {
        return $this->expressionBuilder->createGetter($property);
    }

    public function createSimpleObjectGetter(Property $property): Getter
    {
        return $this->expressionBuilder->createSimpleObjectGetter($property);
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return $this->expressionBuilder->getSourceType($blueprint);
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return $this->expressionBuilder->getOutputType($blueprint);
    }
}
