<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Getter;
use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Modificator\PBaszakMessengerMapper;
use PBaszak\MessengerMapperBundle\Expression\Setter;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ReflectionClassExpressionBuilder implements GetterInterface, SetterInterface
{
    public function __construct(
        public array $modificators = [
            new PBaszakMessengerMapper(),
        ]
    ) {
    }

    private static $initialExpressionIds = [];
    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        if (in_array($initialExpressionId, self::$initialExpressionIds)) {
            return new InitialExpression(
                sprintf(
                    "if (!$%s instanceof %s) throw new \InvalidArgumentException('Incoming data must be an %s.');\n",
                    InitialExpression::VARIABLE_NAME,
                    $blueprint->reflection->getName(),
                    $blueprint->reflection->getName(),
                )
            );
        }

        return new InitialExpression(
            sprintf(
                "if (!$%s instanceof %s) throw new \InvalidArgumentException('Incoming data must be an %s.');\n" .
                "$%s = new \ReflectionClass(%s::class);\n",
                InitialExpression::VARIABLE_NAME,
                $blueprint->reflection->getName(),
                $blueprint->reflection->getName(),
                $this->getReflectionClassVariableName($blueprint),
                $blueprint->reflection->getName()
            )
        );
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        if (in_array($initialExpressionId, self::$initialExpressionIds)) {
            return new InitialExpression(
                sprintf(
                    "$%s = $%s->newInstanceWithoutConstructor();\n",
                    InitialExpression::VARIABLE_NAME,
                    $this->getReflectionClassVariableName($blueprint)
                )
            );
        }
        
        return new InitialExpression(
            sprintf(
                "$%s = new \ReflectionClass(%s::class);\n" .
                "$%s = $%s->newInstanceWithoutConstructor();\n",
                $this->getReflectionClassVariableName($blueprint),
                $blueprint->reflection->getName(),
                InitialExpression::VARIABLE_NAME,
                $this->getReflectionClassVariableName($blueprint)
            )
        );
    }

    public function createGetter(Property $property): Getter
    {
        return new Getter(
            sprintf(
                '$%s->getProperty(\'%s\')->getValue($%s)',
                $this->getReflectionClassVariableName($property),
                $property->originName,
                Getter::SOURCE_VARIABLE_NAME,
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
                "$%s->getProperty('%s')->setValue($%s, %s);\n",
                $this->getReflectionClassVariableName($property),
                $property->originName,
                Setter::TARGET_VARIABLE_NAME,
                Setter::GETTER_EXPRESSION,
            )
        );
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                "$%s->getProperty('%s')->setValue($%s, (\$a = %s) instanceof %s ? \$a : new %s(\$a));\n",
                $this->getReflectionClassVariableName($property),
                $property->originName,
                Setter::TARGET_VARIABLE_NAME,
                Setter::GETTER_EXPRESSION,
                $property->getClassType(),
                $property->getClassType(),
            )
        );
    }

    private function getReflectionClassVariableName(Blueprint|Property $blueprintOrProperty): string
    {
        $className = $blueprintOrProperty instanceof Blueprint
            ? $blueprintOrProperty->reflection->getName()
            : $blueprintOrProperty->reflection->getDeclaringClass()->getName();

        return sprintf(
            'ref_%s',
            hash('crc32', $className)
        );
    }
}
