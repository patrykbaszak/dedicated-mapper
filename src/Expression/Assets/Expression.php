<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Properties\Property;

class Expression
{
    public const VAR_VARIABLE = '{{var}}';

    private string $getterExpression;
    /** @var string[] */
    private array $callbacksExpression = [];
    private string $setterExpression;
    /** @var string[] */
    private array $valueNotFoundExpressions = [];

    /**
     * @param Modificator[] $modificators
     * @param mixed[] $defaultValues
     * @param MappingCallback[] $callbacks
     * @param bool $throwExceptionOnMissingRequiredValue If `false` then property without defeault value 
     *                  will be skipped, but code from valueNotFoundExpressions will be executed anyway.
     *                 If `true` then `if isset` will not be added to the code.
     */
    public function __construct(
        private Getter $getter,
        private Setter $setter,
        private array $modificators = [],
        private array $callbacks = [],
        private bool $throwExceptionOnMissingRequiredValue = false,
        private string $source = 'data',
        private string $target = 'output',
        private string $var = 'var',
    ) {}

    public function build(Property $source, Property $target): self
    {
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

        $isVarVariableUsed = $this->getter->isVarVariableUsed;
        $simpleObjectAttr = $isSimpleObject ? $target->getPropertySimpleObjectAttribute() : null;
        $hasSimpleObjectDeconstructor = (bool) $simpleObjectAttr?->deconstructor;

        $this->setterExpression = $this->setter->getExpression(
            $isSimpleObject,
            $hasSimpleObjectDeconstructor,
            $isVarVariableUsed,
        );

        if (strpos($this->getterExpression, Getter::SETTER_EXPRESSION)) {
            $this->getterExpression = str_replace(
                Getter::SETTER_EXPRESSION,
                $this->setterExpression,
                $this->getterExpression,
            );
        }
    }

    public function toString(): string
    {
        do {
            str_replace(
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
                ],
                [
                    $this->source,
                    $this->setterExpression,
                    var_export($target->getDefaultValue(), true),
                    $simpleObjectAttr?->getConstructorExpression($target->getClassType()),
                    implode("\n", $this->callbacks),
                    implode("\n", $this->valueNotFoundExpressions),
                    $this->var,
                    $this->getter->getSimpleGetter(),
                    $this->target,
                ],
                $this->getterExpression,
            );
        }
        while (): 
    }
}
