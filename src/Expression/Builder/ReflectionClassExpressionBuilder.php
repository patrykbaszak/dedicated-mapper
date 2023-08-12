<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Builder;

use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Getter;
use PBaszak\DedicatedMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapperBundle\Expression\Assets\Setter;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;

class ReflectionClassExpressionBuilder extends AbstractBuilder implements SetterInterface, GetterInterface
{
    /** @var string[] */
    protected static $initialExpressionIds = [];

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        if (in_array($functionId, array_keys(self::$initialExpressionIds)) && $blueprint->reflection->getName() === self::$initialExpressionIds[$functionId]) {
            return new InitialExpression('');
        }

        self::$initialExpressionIds[$functionId] = $blueprint->reflection->getName();

        return new InitialExpression(
            sprintf(
                "$%s = new \ReflectionClass(%s::class);\n",
                $this->getReflectionClassVariableName($blueprint),
                $blueprint->reflection->getName()
            )
        );
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        if (in_array($functionId, array_keys(self::$initialExpressionIds)) && $blueprint->reflection->getName() === self::$initialExpressionIds[$functionId]) {
            return new InitialExpression(
                sprintf(
                    "/** @var %s $%s */\n".
                    "$%s = $%s->newInstanceWithoutConstructor();\n",
                    $blueprint->reflection->getName(),
                    '{{target}}',
                    '{{target}}',
                    $this->getReflectionClassVariableName($blueprint)
                )
            );
        }

        self::$initialExpressionIds[$functionId] = $blueprint->reflection->getName();

        return new InitialExpression(
            sprintf(
                "$%s = new \ReflectionClass(%s::class);\n".
                "/** @var %s $%s */\n".
                "$%s = $%s->newInstanceWithoutConstructor();\n",
                $this->getReflectionClassVariableName($blueprint),
                $blueprint->reflection->getName(),
                $blueprint->reflection->getName(),
                '{{target}}',
                '{{target}}',
                $this->getReflectionClassVariableName($blueprint)
            )
        );
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return $blueprint->reflection->getName();
    }

    public function getTargetType(Blueprint $blueprint): string
    {
        return $blueprint->reflection->getName();
    }

    /**
     * 0 => hasDedicatedGetter
     * 1 => throwExceptionOnMissingRequiredValue
     * 2 => hasDefaultValue
     * 3 => hasCallbacks
     * 4 => hasValueNotFoundCallbacks
     * 5 => isCollection.
     *
     * @return Getter
     *
     * Placeholders list:
     * {{setterAssignment:var}}
     * {{setterAssignment:basic}}
     * {{setterAssignment:basic:default}}
     *
     * {{var}}
     * {{source}}
     * {{defaultValue}}
     * {{dedicatedGetter}}
     * {{callbacks}}
     * {{notFoundCallbacks}}
     * {{preAssignmentExpression}}
     */
    public function getGetter(Property $property): Getter
    {
        $name = $property->originName;
        $property->options['name'] = $name;
        $reflectionClass = $this->getReflectionClassVariableName($property);

        $expressions = [];
        $expressionTemplates = [];
        for ($i = 0; $i < 128; ++$i) {
            $key = str_pad(decbin($i), 7, '0', STR_PAD_LEFT);
            $hasDedicatedGetter = '1' === $key[0];
            $throwExceptionOnMissingRequiredValue = '1' === $key[1];
            $hasDefaultValue = '1' === $key[2];
            $hasCallbacks = '1' === $key[3];
            $hasValueNotFoundCallbacks = '1' === $key[4];
            $isCollection = '1' === $key[5];
            $preAssignmentExpression = '1' === $key[6];

            if ($hasDefaultValue) {
                $throwExceptionOnMissingRequiredValue = true;
                $hasValueNotFoundCallbacks = false;
            }

            $template = $this->getGetterExpressionTemplate(
                $throwExceptionOnMissingRequiredValue,
                $hasDedicatedGetter,
                $hasDefaultValue,
                $hasCallbacks,
                $hasValueNotFoundCallbacks,
                $isCollection,
                $preAssignmentExpression
            );

            $expressions[$key] = [
                '{{getterAssignment:item}}' => '$item',
                '{{getterAssignment:var}}' => '${{var}}',
                '{{getterAssignment:basic}}' => $preAssignmentExpression ? '${{var}}' : "\${$reflectionClass}->getProperty('{$name}')->getValue(\${{source}})",
                '{{getterAssignment:basic:default}}' => '{{existsStatement}} ? {{getterAssignment:basic}} : {{defaultValue}}',
                '{{existsStatement}}' => "\${$reflectionClass}->getProperty('{$name}')->isInitialized(\${{source}})",
                '{{sourceIteratorAssignment}}' => '{{getterAssignment:basic}}',
                '{{varAssignment:basic}}' => $preAssignmentExpression ? '' : "\${{var}} = {{getterAssignment:basic}};\n",
                '{{varAssignment:basic:default}}' => $preAssignmentExpression ? "\${{var}} ??= {{defaultValue}};\n" : "\${{var}} = {{getterAssignment:basic:default}};\n",
                '{{varAssignnmet:item}}' => "\${{var}} = \$item;\n",
                '{{varAssignment:dedicated}}' => "\${{var}} = {{dedicatedGetter}};\n",
                '{{varAssignment:dedicated:default}}' => "if ({{existsStatement}} && {{defaultValue}} !== {{getterAssignment:basic}}) {\n"
                    ."\t\${{var}} = {{dedicatedGetter}};\n"
                    ."} else {\n"
                    ."\t\${{var}} = {{defaultValue}};\n"
                    ."}\n",
            ];

            $vars = array_merge($expressions[$key], ['{{name}}' => $name]);
            $expressionTemplates[$key] = str_replace(array_keys($vars), array_values($vars), $template);
        }

        return new Getter($expressionTemplates, $expressions);
    }

    /**
     * 0 => isCollection
     * 1 => hasFunction
     * 2 => hasPathUsed
     * 3 => isVarVariableUsed.
     *
     * @return Setter
     *
     * Placeholders list:
     * {{getterExpression}}
     * {{getterAssignment:var}}
     * {{deconstructorCall}}
     * {{getterAssignment:basic}}
     * {{getterAssignment:basic:default}}
     * {{sourceIteratorAssignment}}
     *
     * {{array}}
     * {{pathName}}
     * {{function}}
     * {{functionVariable}}
     * {{target}}
     */
    public function getSetter(Property $property): Setter
    {
        $name = $property->originName;
        $reflectionClass = $this->getReflectionClassVariableName($property);
        $property->options['name'] = $name;

        $expressionTemplates = [];
        $expressions = [];
        for ($i = 0; $i < 32; ++$i) {
            $key = str_pad(decbin($i), 5, '0', STR_PAD_LEFT);
            $isCollection = '1' === $key[0];
            $hasFunction = '1' === $key[1];
            $hasPathUsed = '1' === $key[2];
            $isVarVariableUsed = '1' === $key[3];
            $hasDeconstructor = '1' === $key[4];

            $template = $this->getSetterExpressionTemplate(
                $isCollection,
                $hasFunction,
            );

            $expressions[$key] = [
                '{{setterAssignment:var}}' => sprintf(
                    $isCollection ? "\${{array}}[\$index] = %s;\n" : "\${$reflectionClass}->getProperty('{$name}')->setValue(\${{target}}, %s);\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : '{{getterAssignment:var}}'.($hasDeconstructor ? '{{deconstructorCall}}' : '')
                ),
                '{{setterAssignment:basic}}' => sprintf(
                    $isCollection ? "\${{array}}[\$index] = %s;\n" : "\${$reflectionClass}->getProperty('{$name}')->setValue(\${{target}}, %s);\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : ($isCollection ? '{{getterAssignment:item}}' : '{{getterAssignment:basic}}').($hasDeconstructor ? '{{deconstructorCall}}' : '')
                ),
                '{{setterAssignment:basic:default}}' => sprintf(
                    $isCollection ? "\${{array}}[\$index] = %s;\n" : "\${$reflectionClass}->getProperty('{$name}')->setValue(\${{target}}, %s);\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : '{{getterAssignment:basic:default}}'
                ),
                '{{targetIteratorInitialAssignment}}' => "\${{array}} = [];\n",
                '{{targetIteratorFinalAssignment}}' => "\${{var}} = \${{array}};\n",
            ];

            foreach ($expressions[$key] as &$value) {
                $value = str_replace('{{name}}', $name, $value);
            }

            $vars = $expressions[$key];
            $expressionTemplates[$key] = str_replace(array_keys($vars), array_values($vars), $template);
        }

        return new Setter($expressionTemplates, $expressions);
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
