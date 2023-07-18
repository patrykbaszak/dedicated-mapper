<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle\Expression;

use PBaszak\DedicatedMapperBundle\Contract\FunctionInterface;
use PBaszak\DedicatedMapperBundle\Contract\GetterInterface;
use PBaszak\DedicatedMapperBundle\Contract\LoopInterface;
use PBaszak\DedicatedMapperBundle\Contract\SetterInterface;
use PBaszak\DedicatedMapperBundle\Expression\Builder\SecondBlueprintExpressionBuilderDecorator;
use PBaszak\DedicatedMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\DedicatedMapperBundle\Mapper;
use PBaszak\DedicatedMapperBundle\Properties\Blueprint;
use PBaszak\DedicatedMapperBundle\Properties\Property;
use Symfony\Component\Uid\Uuid;

class ExpressionBuilder
{
    protected Mapper $mapper;
    protected static int $seed = 0;

    public function __construct(
        protected Blueprint $blueprint,
        protected GetterInterface $getterBuilder,
        protected SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder,
        protected LoopInterface $loopBuilder,
        protected ?array $groups = null,
    ) {
    }

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function applyModificators(array $modificators): self
    {
        if ($this->getterBuilder instanceof SecondBlueprintExpressionBuilderDecorator) {
            $this->getterBuilder->applyModificators($this->blueprint, $this->getterBuilder, $this->setterBuilder, $this->groups, $modificators);
        }

        if ($this->setterBuilder instanceof SecondBlueprintExpressionBuilderDecorator) {
            $this->setterBuilder->applyModificators($this->blueprint, $this->getterBuilder, $this->setterBuilder, $this->groups, $modificators);
        }

        foreach ($modificators as $modificator) {
            $modificator->init($this->blueprint, $this->getterBuilder, $this->setterBuilder);
        }

        return $this;
    }

    public function createExpression(bool $throwException = false): self
    {
        $this->mapper = new Mapper(
            sprintf(
                'return %s;',
                $this->createBlueprintExpression(
                    $this->blueprint,
                    'data',
                    'output',
                    $throwException
                )
            )
        );

        return $this;
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }

    protected function createBlueprintExpression(
        Blueprint $blueprint,
        string $sourceVariableName,
        string $targetVariableName,
        bool $throwException,
    ): string {
        $function = $this->functionBuilder->getFunction();

        $functionBody = $this->createFunctionBodyExpression(
            $blueprint,
            $sourceVariableName,
            $targetVariableName,
            $throwException,
        );

        return $function->toString(
            $sourceVariableName,
            $targetVariableName,
            $functionBody,
            $this->getterBuilder->getSourceType($blueprint),
            $this->setterBuilder->getOutputType($blueprint),
        );
    }

    protected function createInitialExpression(Blueprint $blueprint, string $sourceVariableName, string $targetVariableName): string
    {
        $initialExpressionId = Uuid::v4()->toRfc4122();

        /** getter have to be first */
        $getter = $this->getterBuilder->getGetterInitialExpression($blueprint, $initialExpressionId);
        $setter = $this->setterBuilder->getSetterInitialExpression($blueprint, $initialExpressionId);

        $initialExpression = array_filter([
            $getter->toString($sourceVariableName),
            $setter->toString($targetVariableName),
        ], fn (string $expression) => !empty($expression));

        return sprintf(
            "%s\n",
            implode("\n", $initialExpression)
        );
    }

    protected function createPropertyExpression(Property $property, string $sourceVariableName, string $targetVariableName, bool $throwException, bool $isSimpleObject): string
    {
        $statement = $this->getterBuilder->getIssetStatement($property, $this->setterBuilder->isPropertyNullable($property) || $this->setterBuilder->hasPropertyDefaultValue($property));
        $getter = $isSimpleObject
            ? $this->getterBuilder->createSimpleObjectGetter($property)
            : $this->getterBuilder->createGetter($property);
        $setter = $isSimpleObject
            ? $this->setterBuilder->createSimpleObjectSetter($property)
            : $this->setterBuilder->createSetter($property);
        $expression = new Expression(
            $getter,
            $setter,
            $statement,
            $this->setterBuilder->getMappingCallbacks($property),
            $throwException,
            $this->setterBuilder->isPropertyNullable($property),
            $this->setterBuilder->hasPropertyDefaultValue($property),
            $this->setterBuilder->getPropertyDefaultValue($property),
        );

        return $expression->toString(
            $sourceVariableName,
            $targetVariableName,
        );
    }

    protected function createFunctionBodyExpression(
        Blueprint $blueprint,
        string $sourceVariableName,
        string $targetVariableName,
        bool $throwException,
    ): string {
        $functionBody = $this->createInitialExpression($blueprint, $sourceVariableName, $targetVariableName);

        foreach ($blueprint->properties as $property) {
            switch ($property->getPropertyType()) {
                case Property::PROPERTY:
                    $functionBody .= $this->createPropertyExpression($property, $sourceVariableName, $targetVariableName, $throwException, false);
                    break;
                case Property::SIMPLE_OBJECT:
                    $functionBody .= $this->createPropertyExpression($property, $sourceVariableName, $targetVariableName, $throwException, true);
                    break;
                case Property::CLASS_OBJECT:
                    if (!$property->blueprint) {
                        throw new \Exception('Class object property must have blueprint.');
                    }
                    $function = $this->functionBuilder->getFunction();
                    $functionName = $this->createUniqueVariableName($property->blueprint);
                    $functionBody .= sprintf(
                        "$%s = %s;\n%s",
                        $functionName,
                        $function->toString(
                            $sourceVariableName.', string $path = \'\'',
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $throwException),
                            $this->getterBuilder->getSourceType($property->blueprint),
                            $this->setterBuilder->getOutputType($property->blueprint),
                        ),
                        $this->setterBuilder->createSetter($property)
                            ->toString(
                                $targetVariableName,
                                sprintf(
                                    '$%s(%s, implode(\'.\', array_filter([$path ?? null, \'%s\'], fn ($p) => null !== $p)))',
                                    $functionName,
                                    $this->getterBuilder->createGetter($property)->toString($sourceVariableName),
                                    $this->getterBuilder->getPropertyName($property)
                                )
                            )
                    );
                    break;
                case Property::COLLECTION:
                    if (!$property->blueprint) {
                        throw new \Exception('Collection property must have blueprint.');
                    }
                    $function = $this->functionBuilder->getFunction();
                    $functionName = $this->createUniqueVariableName($property->blueprint);
                    $collectionItemVariableName = $this->createUniqueVariableName($property->blueprint);
                    $collectionOutputVariableName = $this->createUniqueVariableName($property->blueprint);
                    $functionBody .= sprintf(
                        "$%s = %s;\n%s",
                        $functionName,
                        $function->toString(
                            $sourceVariableName.', string $path = \'\'',
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $throwException),
                            $this->getterBuilder->getSourceType($property->blueprint),
                            $this->setterBuilder->getOutputType($property->blueprint),
                        ),
                        $this->loopBuilder->getLoop()->toString(
                            $collectionOutputVariableName,
                            $this->getterBuilder->createGetter($property)->toString($sourceVariableName),
                            $collectionItemVariableName,
                            sprintf(
                                '$%s[] = %s;',
                                $collectionOutputVariableName,
                                sprintf(
                                    '$%s($%s, implode(\'.\', array_filter([$path ?? null, $index], fn ($p) => null !== $p)))',
                                    $functionName,
                                    $collectionItemVariableName
                                )
                            ),
                            $this->setterBuilder->createSetter($property)
                                ->toString(
                                    $targetVariableName,
                                    sprintf(
                                        '$%s',
                                        $collectionOutputVariableName,
                                    )
                                )
                        ),
                    );
                    break;
                case Property::SIMPLE_OBJECT_COLLECTION:
                    if (!$property->blueprint) {
                        throw new \Exception('Collection property must have blueprint.');
                    }
                    $function = $this->functionBuilder->getFunction();
                    $functionName = $this->createUniqueVariableName($property->blueprint);
                    $collectionItemVariableName = $this->createUniqueVariableName($property->blueprint);
                    $collectionOutputVariableName = $this->createUniqueVariableName($property->blueprint);
                    $functionBody .= sprintf(
                        "$%s = %s;\n%s",
                        $functionName,
                        $function->toString(
                            $sourceVariableName.', string $path = \'\'',
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $throwException),
                            $this->getterBuilder->getSourceType($property->blueprint),
                            $this->setterBuilder->getOutputType($property->blueprint),
                        ),
                        $this->loopBuilder->getLoop()->toString(
                            $collectionOutputVariableName,
                            $this->getterBuilder->createGetter($property)->toString($sourceVariableName),
                            $collectionItemVariableName,
                            sprintf(
                                '$%s[] = %s;',
                                $collectionOutputVariableName,
                                sprintf(
                                    '$%s($%s, implode(\'.\', array_filter([$path ?? null, $index], fn ($p) => null !== $p)))',
                                    $functionName,
                                    $collectionItemVariableName
                                )
                            ),
                            $this->setterBuilder->createSetter($property)
                                ->toString(
                                    $targetVariableName,
                                    sprintf(
                                        'new %s($%s)',
                                        $property->getClassType(),
                                        $collectionOutputVariableName,
                                    )
                                )
                        ),
                    );
                    break;
                default:
                    throw new \Exception('Unknown property type.');
            }
        }

        return $functionBody;
    }

    /** @var string[] */
    private static array $usedVariableNames = [];

    private function createUniqueVariableName(Blueprint $blueprint): string
    {
        do {
            $variableName = hash('crc32', $blueprint->reflection->getName().self::$seed++, false);
        } while (in_array($variableName, self::$usedVariableNames));

        self::$usedVariableNames[] = $variableName = 'var_'.$variableName;

        return $variableName;
    }
}
