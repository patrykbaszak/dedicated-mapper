<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression\Assets;

use PBaszak\DedicatedMapperBundle\Attribute\MappingCallback;
use PBaszak\DedicatedMapperBundle\Contract\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Properties\Property;

class Expression
{
    public const VAR_VARIABLE = '{{var}}';

    private Property $sourceProperty;
    private Property $targetProperty;

    private string $getterExpression;
    /** @var string[] */
    private array $callbacksExpression = [];
    private string $setterExpression;
    /** @var string[] */
    private array $valueNotFoundExpressions = [];

    /**
     * @param ModificatorInterface[] $modificators
     * @param MappingCallback[]      $callbacks
     * @param bool                   $throwExceptionOnMissingRequiredValue If `false` then property without defeault value
     *                                                                     will be skipped, but code from valueNotFoundExpressions will be executed anyway.
     *                                                                     If `true` then `if isset` will not be added to the code.
     */
    public function __construct(
        private Getter $getter,
        private Setter $setter,
        private ?FunctionExpression $function = null,
        private array $modificators = [],
        private array $callbacks = [],
        private bool $isCollection = false,
        private bool $throwExceptionOnMissingRequiredValue = false,
        private string $source = 'data',
        private string $target = 'output',
        private string $var = 'var',
        private ?string $functionVar = null
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
        $this->callbacksExpression = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? null : $callback->callback, $target->getSortedCallbacks()));
        $this->valueNotFoundExpressions = array_filter(array_map(fn (MappingCallback $callback) => $callback->isValueNotFoundCallback ? $callback->callback : null, $target->getSortedCallbacks()));

        /** Getter */
        $isSimpleObject = Property::SIMPLE_OBJECT === $target->getPropertyType();
        $hasDefaultValue = $target->hasDefaultValue();
        $hasCallbacks = !empty($this->callbacks);
        $hasValueNotFoundCallbacks = !empty($this->valueNotFoundExpressions);

        $this->getterExpression = $this->getter->getExpression(
            $isSimpleObject,
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
                ],
                [
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
                ],
                $expr
            );
        } while (false !== strpos($expr, '{{'));

        return $expr;
    }
}
