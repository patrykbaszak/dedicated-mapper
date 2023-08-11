<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

use LogicException;
use PBaszak\DedicatedMapperBundle\Attribute\InitialValueCallback;
use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Contract\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Properties\Property;

class Expression
{
    public const VAR_VARIABLE = '{{var}}';

    private Property $sourceProperty;
    private Property $targetProperty;

    /** @var string[] */
    public array $expressionPatterns = [];
    public string $getterExpressionTemplate;
    public string $setterExpressionTemplate;
    /** @var string[] */
    public array $callbacksExpression = [];
    /** @var string[] */
    public array $collectionItemCallbacksExpression = [];
    /** 
     * There is no not found callbacks for collection items.
     * @var string[] 
     */
    public array $valueNotFoundExpressions = [];

    /**
     * @param ModificatorInterface[] $modificators
     * @param MappingCallback[]      $callbacks
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
        public ?string $functionVar = null
    ) {
        if (!empty($this->function) && null === $this->functionVar) {
            throw new \LogicException('Function variable name must be provided when function is set.');
        }
    }

    public function build(Property $source, Property $target): self
    {
        $this->sourceProperty = $source;
        $this->targetProperty = $target;

        $this->applyModificators($source, $target);
        $this->applyCallbacks($source, $target);

        $hasFunction = !empty($this->function);
        $isPathUsed = (bool) $this->function?->pathVariable;

        if ($target->isCollection()) {
            $collectionItemArgs = [$target->hasDedicatedInitCallback(true), true, false, !empty($this->collectionItemCallbacksExpression), false, true];
            $collectionItemGetterExpressionTemplate = $this->getter->getExpressionTemplate(...$collectionItemArgs);
            $collectionItemExpressions = $this->getter->getExpressions(...$collectionItemArgs); 
            $isCollectionItemVarVariableUsed = false !== strpos($collectionItemGetterExpressionTemplate, '{{setterAssignment:var}}');
            
            $collectionItemArgs = [true, $hasFunction, $isPathUsed, $isCollectionItemVarVariableUsed];
            $collectionItemSetterExpressionTemplate = $this->setter->getExpressionTemplate(...$collectionItemArgs);
            $collectionItemExpressions = array_merge(
                $collectionItemExpressions,
                $this->setter->getExpressions(...$collectionItemArgs)
            );

            $expr = str_replace(
                array_keys($collectionItemExpressions),
                array_values($collectionItemExpressions),
                $collectionItemSetterExpressionTemplate
            );

            $hasFunction = false;
        }

        // $args = [$target->hasDedicatedInitCallback(false), $this->throwExceptionOnMissingRequiredValue, $target->hasDefaultValue(), !empty($this->callbacksExpression), !empty($this->valueNotFoundExpressions), false];
        // $expressionTemplate = $this->getter->getExpressionTemplate(...$collectionArgs);
        // $expressions = $this->getter->getExpression(...$collectionArgs);
        // $isVarVariableUsed = false !== strpos($collectionExpressionTemplate, '{{setterAssignment:var}}');

        return $this;
    }

    public function toString(): string
    {
        $isSimpleObject = Property::SIMPLE_OBJECT === $this->targetProperty->getPropertyType();
        $simpleObjectAttr = $isSimpleObject ? $this->targetProperty->getPropertySimpleObjectAttribute() : null;
        $hasSetterPlaceholder = false !== strpos($this->getterExpression, '{{setter}}');
        $expr = $hasSetterPlaceholder ? $this->getterExpression : $this->setterExpression;

        if (!$hasSetterPlaceholder) {
            $expr = str_replace('{{getter}}', $this->getterExpression, $expr);
        }

        $args = [
            $this->source,
            $this->setterExpression,
            var_export($this->targetProperty->getDefaultValue(), true),
            $simpleObjectAttr?->getConstructorExpression($this->targetProperty->getClassType()),
            implode("\n", $this->callbacksExpression),
            implode("\n", $this->valueNotFoundExpressions),
            $this->var,
            $this->getter->getSimpleGetter(),
            $this->target,
            $simpleObjectAttr?->getDeconstructorExpression(),
            $this->function?->toString(),
            $this->functionVar,
            $this->function?->pathVariable,
        ];

        do {
            $expr = str_replace(
                [
                    Getter::SOURCE_VARIABLE_NAME,
                    Getter::SETTER_EXPRESSION,
                    Getter::DEFAULT_VALUE_EXPRESSION,
                    Getter::SIMPLE_OBJECT_EXPRESSION,
                    Getter::CALLBACKS_EXPRESSION,
                    Getter::VALUE_NOT_FOUND_EXPRESSIONS,
                    self::VAR_VARIABLE,
                    Setter::GETTER_EXPRESSION,
                    Setter::TARGET_VARIABLE,
                    Setter::SIMPLE_OBJECT_DECONSTRUCTOR,
                    Setter::FUNCTION_DECLARATION,
                    Setter::FUNCTION_VARIABLE,
                    Functions::PATH_NAME,
                ],
                $args,
                $expr
            );
        } while (false !== strpos($expr, '{{'));

        return $expr;
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
}
