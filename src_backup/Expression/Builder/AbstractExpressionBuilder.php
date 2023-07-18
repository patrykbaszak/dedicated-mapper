<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Builder;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Attribute\SimpleObject;
use PBaszak\DedicatedMapperBundle\Expression\InitialExpression;
use PBaszak\DedicatedMapperBundle\Expression\Setter;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

abstract class AbstractExpressionBuilder
{
    /** @var string[] */
    protected static $initialExpressionIds = [];

    public function getPropertyName(Property $property): string
    {
        return $property->options['name'] ?? $property->originName;
    }

    public function isPropertyNullable(Property $property): bool
    {
        return $property->isNullable();
    }

    public function hasPropertyDefaultValue(Property $property): bool
    {
        return $property->hasDefaultValue();
    }

    public function getPropertyDefaultValue(Property $property): mixed
    {
        return $property->getDefaultValue();
    }

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

    /**
     * @return string[]
     */
    public function getMappingCallbacks(Property $property): array
    {
        return array_map(fn (MappingCallback $callback) => $callback->callback, $property->getSortedCallbacks());
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
