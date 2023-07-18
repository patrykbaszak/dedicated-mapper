<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Builder;

use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Getter;
use PBaszak\DedicatedMapperBundle\Expression\InitialExpression;
use PBaszak\DedicatedMapperBundle\Expression\Setter;
use PBaszak\DedicatedMapperBundle\Expression\Statement;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

class ReflectionClassExpressionBuilder extends AbstractExpressionBuilder implements GetterInterface, SetterInterface
{
    public function getPropertyName(Property $property): string
    {
        return $property->originName;
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return $blueprint->reflection->getName();
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return $blueprint->reflection->getName();
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        if (in_array($initialExpressionId, self::$initialExpressionIds)) {
            return new InitialExpression('');
        }

        return new InitialExpression(
            sprintf(
                "$%s = new \ReflectionClass(%s::class);\n",
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
                    "/** @var %s $%s */\n".
                    "$%s = $%s->newInstanceWithoutConstructor();\n",
                    $blueprint->reflection->getName(),
                    InitialExpression::VARIABLE_NAME,
                    InitialExpression::VARIABLE_NAME,
                    $this->getReflectionClassVariableName($blueprint)
                )
            );
        }

        return new InitialExpression(
            sprintf(
                "$%s = new \ReflectionClass(%s::class);\n".
                "/** @var %s $%s */\n".
                "$%s = $%s->newInstanceWithoutConstructor();\n",
                $this->getReflectionClassVariableName($blueprint),
                $blueprint->reflection->getName(),
                $blueprint->reflection->getName(),
                InitialExpression::VARIABLE_NAME,
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
                $this->getPropertyName($property),
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
                $this->getPropertyName($property),
                Setter::TARGET_VARIABLE_NAME,
                Setter::GETTER_EXPRESSION,
            )
        );
    }

    public function createSimpleObjectSetter(Property $property): Setter
    {
        return new Setter(
            sprintf(
                "$%s->getProperty('%s')->setValue($%s, %s);\n",
                $this->getReflectionClassVariableName($property),
                $this->getPropertyName($property),
                Setter::TARGET_VARIABLE_NAME,
                $this->getSimpleObjectSetterExpression($property)
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

    public function getIssetStatement(Property $property, bool $hasDefaultValue): Statement
    {
        if ($hasDefaultValue) {
            return new Statement(
                sprintf(
                    "if (\$%s->getProperty('%s')->isInitialized(\$%s)) {\n".
                    "\t\$%s = %s;\n".
                    "\t%s".
                    "} else if (\$%s->getProperty('%s')->getType()?->allowsNull()) {\n".
                    "\t\$%s->getProperty('%s')->setValue(\$%s, null);\n".
                    "\t\$%s = %s;\n".
                    "\t%s".
                    "}\n",
                    $this->getReflectionClassVariableName($property),
                    $this->getPropertyName($property),
                    Statement::SOURCE_VARIABLE_NAME,
                    Statement::VARIABLE_NAME,
                    Statement::GETTER,
                    Statement::CODE,
                    $this->getReflectionClassVariableName($property),
                    $this->getPropertyName($property),
                    $this->getReflectionClassVariableName($property),
                    $this->getPropertyName($property),
                    Statement::SOURCE_VARIABLE_NAME,
                    Statement::VARIABLE_NAME,
                    Statement::GETTER,
                    Statement::CODE,
                )
            );
        }

        return new Statement(
            sprintf(
                "if (\$%s->getProperty('%s')->isInitialized(\$%s)) {\n".
                "\t\$%s = %s;\n".
                "\t%s".
                "}\n",
                $this->getReflectionClassVariableName($property),
                $this->getPropertyName($property),
                Statement::SOURCE_VARIABLE_NAME,
                Statement::VARIABLE_NAME,
                Statement::GETTER,
                Statement::CODE,
            )
        );
    }
}
