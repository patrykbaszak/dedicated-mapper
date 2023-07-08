<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

class AnonymousObjectExpressionBuilder extends AbstractExpressionBuilder implements GetterInterface, SetterInterface
{
    public function getSourceType(Blueprint $blueprint): string
    {
        return 'object';
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return 'object';
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return new InitialExpression(
            sprintf(
                '$%s = (object)[];',
                InitialExpression::VARIABLE_NAME,
            )
        );
    }

    public function createGetter(Property $property): Getter
    {
        $getter = sprintf(
            '$%s->%s',
            Getter::SOURCE_VARIABLE_NAME,
            $property->originName
        );

        if ($property->hasDefaultValue() && 'null' !== ($var = var_export($property->getDefaultValue(), true))) {
            $getter = sprintf(
                '%s ?? %s',
                $getter,
                $var
            );
        }

        if ($property->isNullable()) {
            $getter = sprintf(
                '%s ?? null',
                $getter
            );
        }

        return new Getter($getter);
    }

    public function createSimpleObjectGetter(Property $property): Getter
    {
        return $this->createGetter($property);
    }

    public function createSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                "$%s->%s = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $property->originName,
                Setter::GETTER_EXPRESSION
            )
        );
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                "$%s->%s = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $property->originName,
                $this->getSimpleObjectSetterExpression($property)
            )
        );
    }
}
