<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Attribute\SimpleObject;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

abstract class AbstractExpressionBuilder
{
    /** @var string[] */
    protected static $initialExpressionIds = [];

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'mixed';
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return null;
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return new InitialExpression('');
    }

    protected function getSimpleObjectSetterExpression(Property $property): string
    {
        $simpleObjectAttr = !empty($x = $property->reflection->getAttributes(SimpleObject::class)) ? $x[0]->newInstance() : null;
        $constructor = $simpleObjectAttr && $simpleObjectAttr->staticConstructor
            ? sprintf('%s::%s(%s)', $property->getClassType(), $simpleObjectAttr->staticConstructor, '%s')
            : sprintf('new %s(%s)', $property->getClassType(), '%s');

        $constructorArguments = $simpleObjectAttr && $simpleObjectAttr->nameOfArgument
            ? sprintf('\'%s\' => %s', $simpleObjectAttr->nameOfArgument, '%s')
            : '%s';

        if ($simpleObjectAttr && $simpleObjectAttr->nameOfArgument) {
            foreach ($simpleObjectAttr->namedArguments as $name => $value) {
                $constructorArguments = sprintf(
                    '%s, \'%s\' => %s',
                    $constructorArguments,
                    $name,
                    var_export($value, true),
                );
            }
        }

        return sprintf(
            '($x = %s) instanceof %s ? $x : %s',
            Setter::GETTER_EXPRESSION,
            $property->getClassType(),
            sprintf(
                $constructor,
                '%s' === $constructorArguments ? '$x' : sprintf('...[%s]', $constructorArguments)
            )
        );
    }
}
