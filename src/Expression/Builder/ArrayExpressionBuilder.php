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

class ArrayExpressionBuilder extends AbstractBuilder implements SetterInterface, GetterInterface
{
    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression("\${{targetName}} = [];\n");
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'array';
    }

    public function getTargetType(Blueprint $blueprint): string
    {
        return 'array';
    }

    /**
     * 0 => hasDedicatedGetter
     * 1 => throwExceptionOnMissingRequiredValue
     * 2 => hasDefaultValue
     * 3 => hasCallbacks
     * 4 => hasValueNotFoundCallbacks
     * 5 => isCollection
     * 
     * @return Getter<string>
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
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        $expressions = [];
        $expressionTemplates = [];
        for ($i = 0; $i < 128; $i++) {
            $key = str_pad(decbin($i), 7, '0', STR_PAD_LEFT);
            $hasDedicatedGetter = $key[0] === '1';
            $throwExceptionOnMissingRequiredValue = $key[1] === '1';
            $hasDefaultValue = $key[2] === '1';
            $hasCallbacks = $key[3] === '1';
            $hasValueNotFoundCallbacks = $key[4] === '1';
            $isCollection = $key[5] === '1';
            $preAssignmentExpression = $key[6] === '1';

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
                '{{getterAssignment:item}}' => "\$item",
                '{{getterAssignment:var}}' => "\${{var}}",
                '{{getterAssignment:basic}}' => $preAssignmentExpression ? "\${{var}}" : "\${{source}}['{$name}']",
                '{{getterAssignment:basic:default}}' => "{{getterAssignment:basic}} ?? {{defaultValue}}",
                '{{existsStatement}}' => "array_key_exists('{$name}', \${{source}})",
                '{{sourceIteratorAssignment}}' => "{{getterAssignment:basic}}",
                '{{varAssignment:basic}}' => $preAssignmentExpression ? "" : "\${{var}} = {{getterAssignment:basic}};\n",
                '{{varAssignment:basic:default}}' => $preAssignmentExpression ? "\${{var}} ??= {{defaultValue}};\n" : "\${{var}} = {{getterAssignment:basic}} ?? {{defaultValue}};\n",
                '{{varAssignnmet:item}}' => "\${{var}} = \$item;\n",
                '{{varAssignment:dedicated}}' => "\${{var}} = {{dedicatedGetter}};\n",
                '{{varAssignment:dedicated:default}}' => "if ({{existsStatement}}) {\n"
                    . "\t\${{var}} = {{dedicatedGetter}};\n"
                    . "} else {\n"
                    . "\t\${{var}} = {{defaultValue}};\n"
                    . "}\n",
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
     * 3 => isVarVariableUsed
     * 
     * @return Setter<string>
     * Placeholders list:
     * {{getterExpression}}
     * {{getterAssignment:var}}
     * {{deconstructorCall}}
     * {{getterAssignment:basic}}
     * {{getterAssignment:basic:default}}
     * {{sourceIteratorAssignment}}
     * 
     * {{pathName}}
     * {{function}}
     * {{functionVariable}}
     * {{target}}
     */
    public function getSetter(Property $property): Setter
    {
        $name = $property->options['name'] ?? $property->originName;
        $property->options['name'] = $name;

        $expressionTemplates = [];
        $expressions = [];
        for ($i = 0; $i < 32; $i++) {
            $key = str_pad(decbin($i), 5, '0', STR_PAD_LEFT);
            $isCollection = $key[0] === '1';
            $hasFunction = $key[1] === '1';
            $hasPathUsed = $key[2] === '1';
            $isVarVariableUsed = $key[3] === '1';
            $hasDeconstructor = $key[4] === '1';

            $template = $this->getSetterExpressionTemplate(
                $isCollection,
                $hasFunction,
            );

            $expressions[$key] = [
                '{{setterAssignment:var}}' => sprintf(
                    $isCollection ? "\${{var}}[\$index] = %s;\n" : "\${{target}}['{$name}'] = %s;\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : "{{getterAssignment:var}}" . ($hasDeconstructor ? "{{deconstructorCall}}" : "")
                ),
                '{{setterAssignment:basic}}' => sprintf(
                    $isCollection ? "\${{var}}[\$index] = %s;\n" : "\${{target}}['{$name}'] = %s;\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : ($isCollection ? '{{getterAssignment:item}}' : '{{getterAssignment:basic}}') . ($hasDeconstructor ? "{{deconstructorCall}}" : "")
                ),
                '{{setterAssignment:basic:default}}' => sprintf(
                    $isCollection ? "\${{var}}[\$index] = %s;\n" : "\${{target}}['{$name}'] = %s;\n",
                    $hasFunction ? $this->getFunctionCallExpressionTemplate($isCollection, $hasPathUsed, $isVarVariableUsed) : "{{getterAssignment:basic:default}}"
                ),
                '{{targetIteratorInitialAssignment}}' => "\${{var}} = [];\n",
                '{{targetIteratorFinalAssignment}}' => "",
            ];

            foreach ($expressions[$key] as &$value) {
                $value = str_replace('{{name}}', $name, $value);
            }
            
            $vars = $expressions[$key];
            $expressionTemplates[$key] = str_replace(array_keys($vars), array_values($vars), $template);
        }

        return new Setter($expressionTemplates, $expressions);
    }
}
