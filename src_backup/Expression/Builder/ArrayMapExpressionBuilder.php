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
                $this->getPropertyMapKey($property)
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
                "$%s['%s'] = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $this->getPropertyMapKey($property),
                Setter::GETTER_EXPRESSION,
            )
        );
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                "$%s['%s'] = %s;\n",
                Setter::TARGET_VARIABLE_NAME,
                $this->getPropertyMapKey($property),
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
                "if (array_key_exists('%s', \$%s)) {\n".
                "\t\$%s = %s;\n".
                "\t%s".
                "}\n",
                $this->getPropertyMapKey($property),
                Statement::SOURCE_VARIABLE_NAME,
                Statement::VARIABLE_NAME,
                Statement::GETTER,
                Statement::CODE,
            )
        );
    }

    protected function getPropertyMapKey(Property $property): string
    {
        return implode(
            $this->separator,
            array_merge(
                array_map(fn (Property $parent) => $this->getPropertyName($parent), $property->getAllParents()),
                [$this->getPropertyName($property)]
            ),
        );
    }
}
