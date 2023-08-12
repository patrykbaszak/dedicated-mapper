<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Contract\GetterInterface;
use PBaszak\DedicatedMapper\Contract\SetterInterface;
use PBaszak\DedicatedMapper\Expression\Getter;
use PBaszak\DedicatedMapper\Expression\InitialExpression;
use PBaszak\DedicatedMapper\Expression\Setter;
use PBaszak\DedicatedMapper\Expression\Statement;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Properties\Property;

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
        return new Getter(
            sprintf(
                '$%s->%s',
                Getter::SOURCE_VARIABLE_NAME,
                $this->getPropertyName($property)
            )
        );
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
                $this->getPropertyName($property),
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
                $this->getPropertyName($property),
                $property->getPropertySimpleObjectAttribute()?->deconstructor
                    ? sprintf(
                        '(%s)->%s(%s)',
                        $this->getSimpleObjectSetterExpression($property),
                        $property->getPropertySimpleObjectAttribute()->deconstructor,
                        $property->getPropertySimpleObjectAttribute()->deconstructorArguments
                            ? sprintf('...%s', var_export($property->getPropertySimpleObjectAttribute()->deconstructorArguments, true))
                            : ''
                    )
                    : $this->getSimpleObjectSetterExpression($property)
            )
        );
    }

    public function getIssetStatement(Property $property, bool $hasDefaultValue): Statement
    {
        if ($hasDefaultValue) {
            return new Statement(
                sprintf(
                    "\$%s = %s;\n".
                    '%s',
                    Statement::VARIABLE_NAME,
                    Statement::GETTER,
                    Statement::CODE,
                )
            );
        }

        return new Statement(
            sprintf(
                "if (property_exists(\$%s, '%s')) {\n".
                    "\t\$%s = %s;\n".
                    "\t%s".
                    "}\n",
                Statement::SOURCE_VARIABLE_NAME,
                $this->getPropertyName($property),
                Statement::VARIABLE_NAME,
                Statement::GETTER,
                Statement::CODE,
            )
        );
    }
}
