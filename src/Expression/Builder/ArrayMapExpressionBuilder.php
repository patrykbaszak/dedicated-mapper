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

/**
 * What the hell is the ArrayMap?
 *
 * Look on this example:
 * [
 *   'baz' => 'qux',
 *   'foo__bar' => 'bar',
 *   'foo__baz' => 'qux',
 * ]
 *
 * this is valid map for this array:
 * [
 *   'foo' => [
 *      'bar' => 'bar',
 *      'baz' => 'qux',
 *   ],
 *   'baz' => 'qux',
 * ]
 *
 * but with separator `__` you can create nested array.
 */
class ArrayMapExpressionBuilder extends AbstractExpressionBuilder implements GetterInterface, SetterInterface
{
    public function __construct(
        protected readonly string $separator = '__',
    ) {
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'array';
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return 'array';
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return new InitialExpression(
            sprintf(
                '$%s = [];',
                InitialExpression::VARIABLE_NAME,
            )
        );
    }

    public function createGetter(Property $property): Getter
    {
        return new Getter(
            sprintf(
                '$%s[\'%s\']',
                Getter::SOURCE_VARIABLE_NAME,
                implode(
                    $this->separator,
                    array_merge(
                        array_map(fn (Property $parent) => $parent->originName, $property->getAllParents()),
                        [$property->originName]
                    ),
                )
            )
        );
    }

    public function createSimpleObjectGetter(Property $property): Getter
    {
        return $this->createGetter($property);
    }

    public function createSetter(Property $property, bool $throwException): Setter
    {
        $getter = Setter::GETTER_EXPRESSION;
        $arrayKey = implode(
            $this->separator,
            array_merge(
                array_map(fn (Property $parent) => $parent->originName, $property->getAllParents()),
                [$property->originName]
            ),
        );

        if ($property->hasDefaultValue() && 'null' !== strtolower($var = var_export($property->getDefaultValue(), true))) {
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

        if (!$throwException && Setter::GETTER_EXPRESSION === $getter) {
            return new Setter(
                sprintf(
                    "if (isset(%s)) $%s['%s'] = %s;\n",
                    $getter,
                    Setter::TARGET_VARIABLE_NAME,
                    $arrayKey,
                    $getter,
                )
            );
        }

        return new Setter(
            sprintf(
                "$%s['%s'] = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $arrayKey,
                $getter
            )
        );
    }

    public function createSimpleObjectSetter(Property $property, bool $throwException): Setter
    {
        $arrayKey = implode(
            $this->separator,
            array_merge(
                array_map(fn (Property $parent) => $parent->originName, $property->getAllParents()),
                [$property->originName]
            ),
        );

        if (!$throwException) {
            return new Setter(
                sprintf(
                    "if (isset(%s)) $%s['%s'] = %s;\n",
                    Setter::GETTER_EXPRESSION,
                    Setter::TARGET_VARIABLE_NAME,
                    $arrayKey,
                    $this->getSimpleObjectSetterExpression($property),
                )
            );
        }

        return new Setter(
            sprintf(
                "$%s['%s'] = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $arrayKey,
                $this->getSimpleObjectSetterExpression($property)
            )
        );
    }
}
