<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\DTO\Properties\Constraint;
use PBaszak\MessengerMapperBundle\DTO\Property;

class ExpressionBuilder implements ExpressionBuilderInterface
{
    private bool $validatorUsed = false;
    private readonly int $mainSourceType;
    private readonly int $mainTargetType;

    /**
     * {@inheritdoc}
     */
    public function buildExpression(
        Property $targetProperty,
        string $sourceVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator
    ): string {
        $this->mainSourceType = $sourceType;
        $this->mainTargetType = $targetType;

        $expression = $this->doBuildExpression(
            $targetProperty,
            $sourceVariableName,
            $targetProperty->name,
            $sourceType,
            $targetType,
            $sourceMapSeparator,
            $targetMapSeparator
        );

        if ($this->validatorUsed) {
            $expression = sprintf('$errors=[];%sif (!empty($errors)) {
                $constraintViolationList = new Symfony\Component\Validator\ConstraintViolationList();
                foreach ($errors as $path => $errorList) {
                    foreach (array_values($errorList) as $constraintViolation) {
                        $constraintViolationList->add(
                            new Symfony\Component\Validator\ConstraintViolation(
                                $constraintViolation->getMessage(),
                                $constraintViolation->getMessageTemplate(),
                                $constraintViolation->getParameters(),
                                $constraintViolation->getRoot(),
                                $path,
                                $constraintViolation->getInvalidValue(),
                                $constraintViolation->getPlural(),
                                $constraintViolation->getCode(),
                                $constraintViolation->getConstraint(),
                                $constraintViolation->getCause(),
                            );
                        );
                    }
                }

                throw new Symfony\Component\Validator\Exception\ValidationFailedException(null, $constraintViolationList);
            }', $expression);
        }

        return sprintf('%sreturn $mapped;', $expression);
    }

    /**
     * @param Property[]|Property $reducedTargetProperties
     */
    private function doBuildExpression(
        array|Property $reducedTargetProperties,
        string $sourceVariableName,
        string $targetVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator
    ): string {
        $expression = [];

        if (!is_array($reducedTargetProperties)) {
            $reducedTargetProperties = [$reducedTargetProperties];
        }

        foreach ($reducedTargetProperties as $property) {
            switch (true) {
                case $property->isCollectionItem:
                    $expression[] = $this->buildCollectionExpression(
                        $property,
                        $sourceVariableName,
                        $targetVariableName,
                        $sourceType,
                        $targetType,
                        $sourceMapSeparator,
                        $targetMapSeparator
                    );
                    break;
                case class_exists($property->type, false):
                    $expression[] = $this->buildObjectExpression(
                        $property,
                        $sourceVariableName,
                        $targetVariableName,
                        $sourceType,
                        $targetType,
                        $sourceMapSeparator,
                        $targetMapSeparator
                    );
                    break;
                default:
                    $expression[] = $this->buildPropertyExpression(
                        $property,
                        $sourceVariableName,
                        $targetVariableName,
                        $sourceType,
                        $targetType,
                        $sourceMapSeparator,
                        $targetMapSeparator
                    );
            }
        }

        return implode('', $expression);
    }

    private function buildCollectionExpression(
        Property $property,
        string $sourceVariableName,
        string $targetVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator
    ): string {
        $expression = [];

        $collectionVariableName = sprintf('%sC', $property->getName());
        $collectionItemVariableName = sprintf('%sCI', $property->getName());
        $expression[] = sprintf('$%s = [];', $collectionVariableName);
        $expression[] = sprintf('foreach ($%s as $%s) {', $sourceVariableName, $collectionItemVariableName);
        $expression[] = $this->buildObjectExpression(
            $property,
            $sourceVariableName,
            $collectionVariableName,
            $sourceType,
            $targetType,
            $sourceMapSeparator,
            $targetMapSeparator
        );
        $expression[] = '}';
        $expression[] = sprintf('$%s = $%s;', $targetVariableName, $collectionVariableName);
        $expression[] = sprintf('unset($%s);', $collectionVariableName);

        return implode('', $expression);
    }

    private function buildObjectExpression(
        Property $property,
        string $sourceVariableName,
        string $targetVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator
    ): string {
        $expression = [];

        if (in_array($targetType, [TypeService::PROPERTY, TypeService::CLASS_OBJECT, TypeService::COLLECTION])) {
            $expression[] = sprintf('if ($%s instanceof %s) {', $sourceVariableName, $property->type);
            $expression[] = sprintf('$%s = $%s;', $targetVariableName, $sourceVariableName);
            if ($property->isNullable) {
                $expression[] = sprintf('} elseif ($%s === null) {', $sourceVariableName);
                $expression[] = sprintf('$%s = null;', $targetVariableName);
            }
            $expression[] = '} else {';

            $constructorParamsVariableName = sprintf('%sCP', $property->getName());
            $constructorParamsExpression = [sprintf('$%s = [];', $constructorParamsVariableName)];
            foreach ($property->getConstructorArguments() as $parameter) {
                $constructorParamsExpression[] = $this->doBuildExpression(
                    $parameter,
                    $sourceVariableName,
                    $constructorParamsVariableName,
                    $sourceType,
                    TypeService::ARRAY,
                    $sourceMapSeparator,
                    null
                );
            }
            if (count($constructorParamsExpression) > 1) {
                $expression[] = implode('', $constructorParamsExpression);
                $expression[] = sprintf('$%s = new %s(...$%s);', $targetVariableName, $property->type, $constructorParamsVariableName);
            } else {
                $expression[] = sprintf('$%s = new %s();', $targetVariableName, $property->type);
            }
            foreach ($property->getNonConstructorArgumentsProperties() as $child) {
                $expression[] = $this->doBuildExpression(
                    $child,
                    $sourceVariableName,
                    $targetVariableName,
                    $sourceType,
                    $targetType,
                    $sourceMapSeparator,
                    $targetMapSeparator
                );
            }
            $expression[] = '}';
        } else {
            foreach ($property->getChildren() as $child) {
                $expression[] = $this->doBuildExpression(
                    $child,
                    $sourceVariableName,
                    $targetVariableName,
                    $sourceType,
                    $targetType,
                    $sourceMapSeparator,
                    $targetMapSeparator
                );
            }
        }

        return implode('', $expression);
    }

    private function buildPropertyExpression(
        Property $property,
        string $sourceVariableName,
        string $targetVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator,
        bool $decorates = true
    ): string {
        $expression = $this->getSourcePath($property, $sourceVariableName, $sourceType, $sourceMapSeparator);
        if ($decorates) {
            $expression = $this->decorateExpression($property, $expression, 'var');
        }
        if (null !== $property->isAssignedTo) {
            $expression = $this->getSimplePropertySetterExpression(
                $property,
                $targetVariableName,
                $targetType,
                $targetMapSeparator,
                $this->getSimplePropertyGetterExpression($property, $property->isAssignedTo, TypeService::PROPERTY, null)
            );
        } else {
            $expression = $this->getSimplePropertySetterExpression($property, $targetVariableName, $targetType, $targetMapSeparator, $expression);
        }

        return $expression;
    }

    private function getSourcePath(Property $property, string $sourceVariableName, int $sourceType, ?string $mapSeparator): string
    {
        if (null !== $mapSeparator) {
            $path = $property->getName();
            $parent = $property->parent;
            while (null !== $parent) {
                $path = $parent->getName().$mapSeparator.$path;
                $parent = $parent->parent;
            }

            return $path;
        }

        $properties = [$property];
        if (null !== $property->parent) {
            $properties[] = $property->parent;
        }

        foreach (array_reverse($properties) as $property) {
            $sourceVariableName = $this->getSimplePropertyGetterExpression($property, $sourceVariableName, $sourceType, $mapSeparator);
        }

        $sourceVariableName = ltrim($sourceVariableName, '$');

        return '$'.$sourceVariableName;
    }

    private function getSimplePropertySetterExpression(Property $property, string $targetVariableName, int $targetType, ?string $targetMapSeparator, string $getterExpression): string
    {
        $targetProperty = Property::TARGET === $property->origin ? $property : $property->getMirrorProperty();
        switch ($targetType) {
            case 1:
                return sprintf('$%s = %s;', $targetVariableName, $getterExpression);
            case 2:
                return sprintf('$%s[%s] = %s;', $targetVariableName, var_export($targetProperty->getName(), true), $getterExpression);
            case 3:
                return sprintf('$%s->%s = %s;', $targetVariableName, $targetProperty->getName(), $getterExpression);
            case 4:
                return sprintf(sprintf('$%s->%s;', $targetVariableName, $targetProperty->getSetter()), $getterExpression);
            case 5:
                if (!$targetMapSeparator) {
                    throw new \LogicException('Cannot get simple setter expression for map without separator.');
                }

                return sprintf('$%s[%s] = %s;', $targetVariableName, var_export($targetProperty->getPath($targetMapSeparator), true), $getterExpression);
            case 6:
                return sprintf('$%s->%s = %s;', $targetVariableName, $targetProperty->getPath($targetMapSeparator), $getterExpression);
            case 7:
                throw new \LogicException('Cannot get simple setter expression for collection.');
            default:
                throw new \LogicException('Unknown target type.');
        }
    }

    private function getSimplePropertyGetterExpression(Property $property, string $sourceVariableName, int $sourceType, ?string $sourceMapSeparator): string
    {
        $sourceProperty = Property::SOURCE === $property->origin ? $property : $property->getMirrorProperty();
        switch ($sourceType) {
            case 1:
                return sprintf('$%s', $sourceVariableName);
            case 2:
                return sprintf('$%s[%s]', $sourceVariableName, var_export($sourceProperty->getName(), true));
            case 3:
                return sprintf('$%s->%s', $sourceVariableName, $sourceProperty->getName());
            case 4:
                return sprintf('$%s->%s', $sourceVariableName, $sourceProperty->getGetter());
            case 5:
                if (!$sourceMapSeparator) {
                    throw new \LogicException('Cannot get simple getter expression for map without separator.');
                }

                return sprintf('$%s[%s]', $sourceVariableName, var_export($sourceProperty->getPath($sourceMapSeparator), true));
            case 6:
                return sprintf('$%s->%s', $sourceVariableName, $sourceProperty->getPath($sourceMapSeparator));
            case 7:
                throw new \LogicException('Cannot get simple getter expression for collection.');
            default:
                throw new \LogicException('Unknown source type.');
        }
    }

    private function decorateExpression(Property $property, string $expression, string $targetVariableName): string
    {
        $expression = $this->decorateExpressionWithDefaultValue($property, $expression);
        $expression = $this->decorateExpressionWithCallbacks($property, $expression);
        $expression = $this->decorateExpressionWithValidator($property, $expression, $targetVariableName);

        return $expression;
    }

    private function decorateExpressionWithDefaultValue(Property $property, string $expression): string
    {
        $defaultPropertyValue = $property->reflection?->hasDefaultValue() ? $property->reflection->getDefaultValue() : null;
        $defaultParameterValue = $property->reflectionParameter?->isOptional() ? $property->reflectionParameter->getDefaultValue() : null;

        if (!$property->isNullable && !$defaultParameterValue && !$defaultPropertyValue) {
            return $expression;
        }

        $defaultValue = $defaultParameterValue ?? $defaultPropertyValue ?? null;

        return sprintf('%s ?? %s', $expression, var_export($defaultValue, true));
    }

    private function decorateExpressionWithValidator(Property $property, string $expression, string $targetVariableName): string
    {
        if (empty($property->validator?->constraints ?? [])) {
            return $expression;
        }

        $this->validatorUsed = true;
        $property->isAssignedTo = $targetVariableName;

        return sprintf(
            'if (count($validationErrors = $validator->validate(($%s = %s), [%s], null) !== 0) {$errors[%s] = $validationErrors];}',
            $targetVariableName,
            $expression,
            implode(
                ',',
                array_map(
                    fn (Constraint $constraint) => sprintf('new %s(...%s)', $constraint->className, var_export($constraint->arguments, true)),
                    $property->validator->constraints
                )
            ),
            $property->getPath('.'),
        );
    }

    private function decorateExpressionWithCallbacks(Property $property, string $expression): string
    {
        $mirrorAs = $property->origin ^ (Property::SOURCE | Property::TARGET);
        if (empty($mirrorCallbacks = $this->getSelfMappingCallbacks($property->getMirrorProperty(), $mirrorAs)) && empty($selfCallbacks = $this->getSelfMappingCallbacks($property, $property->origin))) {
            return $expression;
        }

        $callbacks = array_merge($selfCallbacks ?? [], $mirrorCallbacks);

        foreach ($this->sortCallbacks($callbacks) as $callback) {
            if (false !== strpos($callback->callback, '%s')) {
                $expression = sprintf($callback->callback, $expression);
            } elseif (false !== strpos($callback->callback, '::')) {
                $expression = sprintf('%s(%s)', $callback->callback, $expression);
            } else {
                throw new \LogicException('Unsupported callback.');
            }
        }

        return $expression;
    }

    /** @return MappingCallback[] */
    public function getSelfMappingCallbacks(Property $property, int $activateOnMapping): array
    {
        $callbacks = [];

        if (null !== $property->mapper) {
            foreach ($property->mapper?->mappingCallbacks ?? [] as $callback) {
                if ($callback->activateOnMapping & $activateOnMapping) {
                    $callbacks[] = $callback;
                }
            }
        }

        return $callbacks;
    }

    /**
     * @param MappingCallback[] $callbacks
     *
     * @return MappingCallback[]
     */
    private function sortCallbacks(array $callbacks): array
    {
        usort($callbacks, function (MappingCallback $a, MappingCallback $b) {
            return $a->priority <=> $b->priority;
        });

        return $callbacks;
    }
}
