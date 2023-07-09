<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;
use Symfony\Component\Uid\Uuid;

class ExpressionBuilder
{
    protected Mapper $mapper;
    protected static int $seed = 0;
    protected string $useStatements = '';

    public function __construct(
        protected Blueprint $blueprint,
        protected GetterInterface $getterBuilder,
        protected SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder,
        protected LoopInterface $loopBuilder,
        protected ?string $group = null,
    ) {
    }

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function applyModificators(array $modificators): self
    {
        foreach ($modificators as $modificator) {
            // $modificator->modify($this->blueprint);
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
                    $this->useStatements,
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
        string $useStatements,
        bool $throwException,
    ): string {
        $function = $this->functionBuilder->getFunction();

        $functionBody = $this->createFunctionBodyExpression(
            $blueprint,
            $sourceVariableName,
            $targetVariableName,
            $useStatements,
            $throwException,
        );

        return $function->toString(
            $sourceVariableName,
            $targetVariableName,
            $functionBody,
            $useStatements,
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

    protected function createSinglePropertyExpression(Property $property, string $sourceVariableName, string $targetVariableName, bool $throwException): string
    {
        $getter = $this->getterBuilder->createGetter($property);
        $setter = $this->setterBuilder->createSetter($property, $throwException);

        return $setter->toString(
            $targetVariableName,
            $getter->toString($sourceVariableName)
        );
    }

    protected function createSimpleObjectExpression(Property $property, string $sourceVariableName, string $targetVariableName, bool $throwException): string
    {
        $getter = $this->getterBuilder->createSimpleObjectGetter($property);
        $setter = $this->setterBuilder->createSimpleObjectSetter($property, $throwException);

        return $setter->toString(
            $targetVariableName,
            $getter->toString($sourceVariableName)
        );
    }

    protected function createFunctionBodyExpression(
        Blueprint $blueprint,
        string $sourceVariableName,
        string $targetVariableName,
        string $useStatements,
        bool $throwException,
    ): string {
        $functionBody = $this->createInitialExpression($blueprint, $sourceVariableName, $targetVariableName);

        foreach ($blueprint->properties as $property) {
            switch ($property->getPropertyType()) {
                case Property::PROPERTY:
                    $functionBody .= $this->createSinglePropertyExpression($property, $sourceVariableName, $targetVariableName, $throwException);
                    break;
                case Property::SIMPLE_OBJECT:
                    $functionBody .= $this->createSimpleObjectExpression($property, $sourceVariableName, $targetVariableName, $throwException);
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
                            $sourceVariableName,
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $useStatements, $throwException),
                            $useStatements,
                            $this->getterBuilder->getSourceType($property->blueprint),
                            $this->setterBuilder->getOutputType($property->blueprint),
                        ),
                        $this->setterBuilder->createSetter($property, true)
                            ->toString(
                                $targetVariableName,
                                sprintf(
                                    '$%s(%s)',
                                    $functionName,
                                    $this->getterBuilder->createGetter($property)->toString($sourceVariableName)
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
                            $sourceVariableName,
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $useStatements, $throwException),
                            $useStatements,
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
                                    '$%s($%s)',
                                    $functionName,
                                    $collectionItemVariableName
                                )
                            ),
                            $this->setterBuilder->createSetter($property, true)
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
                            $sourceVariableName,
                            $targetVariableName,
                            $this->createFunctionBodyExpression($property->blueprint, $sourceVariableName, $targetVariableName, $useStatements, $throwException),
                            $useStatements,
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
                                    '$%s($%s)',
                                    $functionName,
                                    $collectionItemVariableName
                                )
                            ),
                            $this->setterBuilder->createSetter($property, true)
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
