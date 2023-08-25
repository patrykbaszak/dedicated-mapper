<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Builder;

use PBaszak\DedicatedMapper\Expression\Assets\FinalExpression;
use PBaszak\DedicatedMapper\Expression\Assets\InitialExpression;
use PBaszak\DedicatedMapper\Properties\Blueprint;

abstract class AbstractBuilder
{
    /**
     * @param class-string $blueprint
     */
    public function __construct(
        protected ?string $blueprint = null,
    ) {
    }

    public function getBlueprint(bool $isCollection = false): ?Blueprint
    {
        return $this->blueprint ? Blueprint::create($this->blueprint, $isCollection) : null;
    }

    public function getSourceType(Blueprint|string $blueprint): string
    {
        return 'mixed';
    }

    public function getTargetType(Blueprint|string $blueprint): string
    {
        return 'mixed';
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression('');
    }

    public function getGetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression
    {
        return new FinalExpression('');
    }

    public function getSetterInitialExpression(Blueprint $blueprint, string $functionId): InitialExpression
    {
        return new InitialExpression('');
    }

    public function getSetterFinalExpression(Blueprint $blueprint, string $functionId): FinalExpression
    {
        return new FinalExpression('');
    }

    /**
     * @return string
     *                Placeholders list:
     *                {{name}}
     *
     * {{setterAssignment:var}}
     * {{setterAssignment:basic}}
     * {{setterAssignment:basic:default}}
     *
     * {{varAssignment:basic}}
     * {{varAssignment:basic:default}}
     * {{varAssignment:dedicated}}
     * {{varAssignment:dedicated:default}}
     *
     * {{callbacks}}
     * {{notFoundCallbacks}}
     * {{preAssignmentExpression}}
     * {{existsStatement}}
     */
    protected function getGetterExpressionTemplate(
        bool $throwExceptionOnMissing,
        bool $hasDedicatedGetter,
        bool $hasDefaultValue,
        bool $hasCallbacks,
        bool $hasNotFoundCallbacks,
        bool $isCollection,
        bool $preAssignment,
    ): string {
        $hasVarAssignment = $hasDedicatedGetter || $hasCallbacks;

        $missingExpression = $hasNotFoundCallbacks
            ? '{{notFoundCallbacks}}'
            : ($throwExceptionOnMissing ? "throw new \Exception('Missing property: `{{name}}`.');\n" : '');
        $hasMissingExpression = ('' !== $missingExpression) && !$hasDefaultValue;

        $preAssignmentExpression = $preAssignment ? '{{preAssignmentExpression}}' : '';

        $varAssignmentExpression = !$hasVarAssignment
            ? ''
            : sprintf(
                '{{varAssignment:%s}}',
                implode(':', array_filter([
                    $hasDedicatedGetter ? 'dedicated' : 'basic',
                    $hasDefaultValue ? 'default' : null,
                ]))
            );

        $setterAssigmentExpression = $hasVarAssignment
            ? '{{setterAssignment:var}}'
            : ($hasDefaultValue ? '{{setterAssignment:basic:default}}' : '{{setterAssignment:basic}}');

        $callbacksExpression = $hasCallbacks ? '{{callbacks}}' : '';

        $successExpression = $preAssignmentExpression.$varAssignmentExpression.$callbacksExpression.$setterAssigmentExpression;
        $failureExpression = $missingExpression;

        if (!$isCollection) {
            if (!$throwExceptionOnMissing && $hasMissingExpression) {
                return "if ({{existsStatement}}) {\n{$successExpression}} else {\n{$failureExpression}}\n";
            }

            if (!$throwExceptionOnMissing && !$hasMissingExpression) {
                return "if ({{existsStatement}}) {\n{$successExpression}}\n";
            }

            if ($throwExceptionOnMissing && $hasMissingExpression) {
                return "if (!{{existsStatement}}) {\n{$failureExpression}}\n{$successExpression}\n";
            }
        }

        return $successExpression;
    }

    /**
     * @return string
     *                Placeholders list:
     *                {{getterExpression}}
     *                {{sourceIteratorAssignment}}
     *
     * {{function}}
     * {{functionVariable}}
     * {{targetIteratorInitialAssignment}}
     * {{targetIteratorFinalAssignment}}
     */
    public function getSetterExpressionTemplate(
        bool $isCollection,
        bool $hasFunction,
    ): string {
        $getterExpression = '{{getterExpression}}';
        $functionDeclarationExpression = $hasFunction ? "\${{functionVariable}} = {{function}};\n" : '';
        $collectionExpression = $isCollection
            ? '{{targetIteratorInitialAssignment}}'
            ."foreach ({{sourceIteratorAssignment}} as \$index => \$item) {\n{$getterExpression}}\n"
            .'{{targetIteratorFinalAssignment}}'
            : '';

        if ($isCollection && $hasFunction) {
            return $functionDeclarationExpression.$collectionExpression;
        }

        if ($isCollection && !$hasFunction) {
            return $collectionExpression;
        }

        if (!$isCollection && $hasFunction) {
            return $functionDeclarationExpression.$getterExpression;
        }

        return $getterExpression;
    }

    /**
     * @return string
     *                Placeholders list:
     *                {{name}}
     *                {{pathName}}
     *                {{getterAssignment:var}}
     *                {{getterAssignment:basic}}
     *                {{functionVariable}}
     */
    public function getFunctionCallExpressionTemplate(
        bool $isCollection,
        bool $hasPathUsed,
        bool $hasVarUsed,
    ): string {
        $functionArguments = implode(', ', array_filter([
            $hasVarUsed ? '{{getterAssignment:var}}' : ($isCollection ? '{{getterAssignment:item}}' : '{{getterAssignment:basic}}'),
            $hasPathUsed ? ($isCollection
                ? '${{pathName}} . ".{{name}}[{$index}]"'
                : '${{pathName}} . ".{{name}}"'
            ) : null,
        ]));

        return sprintf(
            '${{functionVariable}}(%s)',
            $functionArguments
        );
    }
}
