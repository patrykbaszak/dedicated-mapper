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

    public string $getterExpression;
    public string $setterExpression;
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

        /* Modificators */
        foreach ($this->modificators as $modificator) {
            $modificator->modifyPropertyExpression($source, $target, $this);
        }

        /* Callbacks */
        $target->callbacks = array_merge($target->callbacks, $this->callbacks);
        $target->collectionItemCallbacks = array_merge($target->collectionItemCallbacks, $this->collectionItemCallbacks);
        $this->callbacksExpression = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? null : $callback->callback, $target->getSortedCallbacks()));
        $this->collectionItemCallbacksExpression = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? null : $callback->callback, $target->getSortedCallbacks(true)));
        $this->valueNotFoundExpressions = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? $callback->callback : null, $target->getSortedCallbacks()));

        /** Getter */
        $hasDedicatedGetter = !empty($target->reflection->getAttributes(InitialValueCallback::class)) || in_array($target->getPropertyType(), [Property::SIMPLE_OBJECT, Property::SIMPLE_OBJECT_COLLECTION, Property::SIMPLE_OBJECTS_SIMPLE_OBJECT_COLLECTION]);
        $hasDefaultValue = $target->hasDefaultValue();
        $hasCallbacks = !empty($this->callbacks);
        $hasValueNotFoundCallbacks = !empty($this->valueNotFoundExpressions);
        $hasCollectionItemCallbacks = !empty($this->collectionItemCallbacksExpression);

        $this->getterExpression = $this->getter->getExpression(
            $hasDedicatedGetter,
            $this->throwExceptionOnMissingRequiredValue,
            $hasDefaultValue,
            $hasCallbacks,
            $hasValueNotFoundCallbacks,
        );

        /** Setter */
        $hasFunction = !empty($this->function);
        $isPathUsed = (bool) $this->function?->pathVariable;
        $isVarVariableUsed = $this->getter->isVarVariableUsed;
        $simpleObjectAttr = $isSimpleObject ? $target->getPropertySimpleObjectAttribute() : null;
        $hasSimpleObjectDeconstructor = (bool) $simpleObjectAttr?->deconstructor;

        $this->setterExpression = $this->setter->getExpression(
            $this->isCollection,
            $hasFunction,
            $isPathUsed,
            $isSimpleObject,
            $hasSimpleObjectDeconstructor,
            $isVarVariableUsed,
        );

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
}
