<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Assets;

use PBaszak\DedicatedMapper\Attribute\MappingCallback;
use PBaszak\DedicatedMapper\Contract\ModificatorInterface;
use PBaszak\DedicatedMapper\Properties\Property;
use PBaszak\DedicatedMapper\Utils\HasNotFilledPlaceholdersTrait;

class Expression
{
    use HasNotFilledPlaceholdersTrait;

    public string $expression;
    /** @var array<string,string> */
    public array $expressionPlaceholders = [];

    /** @var string[] */
    public array $callbacksExpression = [];
    /** @var string[] */
    public array $collectionItemCallbacksExpression = [];
    /**
     * There is no not found callbacks for collection items.
     *
     * @var string[]
     */
    public array $valueNotFoundExpressions = [];

    /**
     * @param ModificatorInterface[] $modificators
     * @param MappingCallback[]      $callbacks
     * @param MappingCallback[]      $collectionItemCallbacks
     * @param bool                   $throwExceptionOnMissingRequiredValue If `false` then property without defeault value
     *                                                                     will be skipped, but code from valueNotFoundExpressions will be executed anyway.
     *                                                                     If `true` then `if isset` will not be added to the code.
     */
    public function __construct(
        public Getter $getter,
        public Setter $setter,
        public ?FunctionExpression $function = null,
        public array $modificators = [],
        public array $callbacks = [],
        public array $collectionItemCallbacks = [],
        public bool $throwExceptionOnMissingRequiredValue = false,
        public string $source = 'data',
        public string $target = 'output',
        public string $var = 'var',
        public ?string $functionVar = null,
        public string $array = 'array',
    ) {
        if (!empty($this->function) && null === $this->functionVar) {
            throw new \LogicException('Function variable name must be provided when function is set.');
        }
    }

    public function build(Property $source, Property $target): self
    {
        $this->applyModificators($source, $target);
        $this->applyCallbacks($source, $target);

        $hasFunction = !empty($this->function);
        $isPathUsed = (bool) $this->function?->pathVariable;

        if ($target->isCollection()) {
            $itemExpressionArgs = [
                $target->hasDedicatedInitCallback(true),
                true,
                false,
                !empty($this->collectionItemCallbacksExpression),
                false,
                true,
                $hasFunction,
                $isPathUsed,
                false,
                $hasItemDeconstructor = (bool) ($target->isSimpleObject(true) && $target->getPropertySimpleObjectAttribute(true)?->deconstructor),
            ];

            [$itemExpression, $itemExpressionPlaceholders] = $this->newExpression(...$itemExpressionArgs);
            if ($itemExpressionArgs[0]) {
                $itemExpressionPlaceholders['{{dedicatedGetter}}'] = $target->getInitialCallbackAttribute(true)?->callback ?? '';
            }
            if ($hasItemDeconstructor) {
                $itemExpressionPlaceholders['{{deconstructorCall}}'] = $target->getPropertySimpleObjectAttribute(true)?->getDeconstructorExpression();
            }

            do {
                $itemExpression = str_replace(array_keys($itemExpressionPlaceholders), array_values($itemExpressionPlaceholders), $itemExpression);
            } while ($this->hasNotFilledPlaceholders(array_keys($itemExpressionPlaceholders), $itemExpression));

            $expressionArgs = [
                $target->hasDedicatedInitCallback(false),
                $this->throwExceptionOnMissingRequiredValue,
                $target->hasDefaultValue(),
                !empty($this->callbacksExpression),
                !empty($this->valueNotFoundExpressions),
                false,
                false,
                $isPathUsed,
                true,
                $hasDeconstructor = (bool) ($target->isSimpleObject(false) && $target->getPropertySimpleObjectAttribute(false)?->deconstructor),
            ];

            [$expression, $expressionPlaceholders] = $this->newExpression(...$expressionArgs);
            $expressionPlaceholders['{{preAssignmentExpression}}'] = $itemExpression;
        } else {
            $expressionArgs = [
                $target->hasDedicatedInitCallback(false),
                $this->throwExceptionOnMissingRequiredValue,
                $target->hasDefaultValue(),
                !empty($this->callbacksExpression),
                !empty($this->valueNotFoundExpressions),
                $target->isCollection(),
                $hasFunction,
                $isPathUsed,
                false,
                $hasDeconstructor = (bool) ($target->isSimpleObject(false) && $target->getPropertySimpleObjectAttribute(false)?->deconstructor),
            ];

            [$expression, $expressionPlaceholders] = $this->newExpression(...$expressionArgs);
        }

        if ($expressionArgs[0]) {
            $expressionPlaceholders['{{dedicatedGetter}}'] = $target->getInitialCallbackAttribute(false)?->callback ?? '';
        }
        if ($expressionArgs[2]) {
            $expressionPlaceholders['{{defaultValue}}'] = var_export($target->getDefaultValue(), true) ?? 'null';
        }
        if ($expressionArgs[3]) {
            $expressionPlaceholders['{{callbacks}}'] = implode("\n", $this->callbacksExpression);
        }
        if ($expressionArgs[4]) {
            $expressionPlaceholders['{{notFoundCallbacks}}'] = implode("\n", $this->valueNotFoundExpressions);
        }
        if ($hasDeconstructor) {
            $expressionPlaceholders['{{deconstructorCall}}'] = $target->getPropertySimpleObjectAttribute(false)?->getDeconstructorExpression() ?? '';
        }

        do {
            $expression = str_replace(array_keys($expressionPlaceholders), array_values($expressionPlaceholders), $expression);
        } while ($this->hasNotFilledPlaceholders(array_keys($expressionPlaceholders), $expression));

        $this->expression = $expression;
        $this->expressionPlaceholders = $expressionPlaceholders;

        return $this;
    }

    public function toString(): string
    {
        $expression = $this->expression;
        $expressionPlaceholders = [
            '{{source}}' => $this->source,
            '{{target}}' => $this->target,
            '{{array}}' => $this->array,
            '{{var}}' => $this->var,
            '{{functionVariable}}' => $this->functionVar,
            '{{function}}' => $this->function?->toString(),
            '{{pathName}}' => $this->function?->pathVariable,
            '{{preAssignmentExpression}}' => '',
        ] + $this->expressionPlaceholders;

        do {
            $expression = str_replace(array_keys($expressionPlaceholders), array_values($expressionPlaceholders), $expression);
        } while ($this->hasNotFilledPlaceholders(array_keys($expressionPlaceholders), $expression));

        return $expression;
    }

    private function applyModificators(Property $source, Property $target): void
    {
        foreach ($this->modificators as $modificator) {
            $modificator->modifyPropertyExpression($source, $target, $this);
        }
    }

    private function applyCallbacks(Property $source, Property $target): void
    {
        $target->callbacks = array_merge($target->callbacks, $this->callbacks);
        $target->collectionItemCallbacks = array_merge($target->collectionItemCallbacks, $this->collectionItemCallbacks);
        $this->callbacksExpression = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? null : $callback->callback, $target->getSortedCallbacks()));
        $this->collectionItemCallbacksExpression = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? null : $callback->callback, $target->getSortedCallbacks(true)));
        $this->valueNotFoundExpressions = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? $callback->callback : null, $target->getSortedCallbacks()));
    }

    /**
     * @return array{0: string, 1: array<string,string>}
     */
    private function newExpression(
        bool $hasDedicatedGetter,
        bool $throwExceptionOnMissingRequiredValue,
        bool $hasDefaultValue,
        bool $hasCallbacks,
        bool $hasValueNotFoundCallbacks,
        bool $isCollection,
        bool $hasFunction,
        bool $hasPathUsed,
        bool $preAssignmentExpression,
        bool $hasDeconstructorCall
    ): array {
        $getterExpressionArgs = [
            $hasDedicatedGetter,
            $throwExceptionOnMissingRequiredValue,
            $hasDefaultValue,
            $hasCallbacks,
            $hasValueNotFoundCallbacks,
            $isCollection,
            $preAssignmentExpression,
        ];

        $getterExpressionTemplate = $this->getter->getExpressionTemplate(...$getterExpressionArgs);
        $expressionPlaceholders = $this->getter->getExpressions(...$getterExpressionArgs);

        $setterExpressionArgs = [
            $isCollection,
            $hasFunction,
            $hasPathUsed,
            false !== strpos($getterExpressionTemplate, '{{setterAssignment:var}}'),
            $hasDeconstructorCall,
        ];

        $setterExpressionTemplate = $this->setter->getExpressionTemplate(...$setterExpressionArgs);
        $expressionPlaceholders = array_merge($expressionPlaceholders, $this->setter->getExpressions(...$setterExpressionArgs));

        $placeholder = '{{getterExpression}}';
        $expression = strpos($setterExpressionTemplate, $placeholder)
            ? str_replace($placeholder, $getterExpressionTemplate, $setterExpressionTemplate)
            : $getterExpressionTemplate;

        do {
            $expression = str_replace(array_keys($expressionPlaceholders), array_values($expressionPlaceholders), $expression);
        } while ($this->hasNotFilledPlaceholders(array_keys($expressionPlaceholders), $expression));

        return [
            $expression,
            $expressionPlaceholders,
        ];
    }
}
