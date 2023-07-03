<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ExpressionBuilder
{
    protected Mapper $mapper;
    protected static int $seed = 0;

    public function __construct(
        protected Blueprint $blueprint,
        protected GetterInterface $getterBuilder,
        protected SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder = new DefaultExpressionBuilder(),
        protected LoopInterface $loopBuilder = new DefaultExpressionBuilder(),
        protected string $originVariableName = 'data',
        protected string $targetVariableName = 'output',
    ) {
    }

    public function createExpression(string $useStatements = ''): void
    {
        $this->mapper = new Mapper(
            sprintf(
                'return %s;',
                $this->createBlueprintExpression(
                    $this->blueprint,
                    'data',
                    'output',
                    $useStatements
                )
            )
        );
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }

    protected function createSinglePropertyExpression(Property $property, string $sourceVariableName, string $targetVariableName): string
    {
        $getter = $this->getterBuilder->createGetter($property);
        $setter = $this->setterBuilder->createSetter($property);

        return $setter->toString(
            $targetVariableName,
            $getter->toString($sourceVariableName)
        );
    }

    protected function createSimpleObjectExpression(Property $property, string $sourceVariableName, string $targetVariableName): string
    {
        $getter = $this->getterBuilder->createSimpleObjectGetter($property);
        $setter = $this->setterBuilder->createSimpleObjectSetter($property);

        return $setter->toString(
            $targetVariableName,
            $getter->toString($sourceVariableName)
        );
    }

    protected function createBlueprintExpression(
        Blueprint $blueprint,
        string $sourceVariableName,
        string $targetVariableName,
        string $useStatements,
    ): string {
        $functionName = $this->createUniqueVariableName($blueprint);
        $function = $this->functionBuilder->getFunction();
        $from = 'data';
        $to = 'output';

        $functionBody = '';
        foreach ($blueprint->properties as $propertyName => $property) {
            /** @var Property $property */
            switch ($property->getPropertyType()) {
                case Property::PROPERTY:
                    $functionBody .= $this->createSinglePropertyExpression($property, $from, $to);
                    break;
                case Property::SIMPLE_OBJECT:
                    $functionBody .= $this->createSimpleObjectExpression($property, $from, $to);
                    break;
                case Property::CLASS_OBJECT:
                    $functionBody .= sprintf(
                        '$%s=%s;%s;',
                        $functionName,
                        $function->toString(
                            $from,
                            $to,
                            $this->createBlueprintExpression($property->blueprint, $from, $to, $useStatements),
                            $useStatements
                        ),
                        $this->setterBuilder->createSetter($property)
                            ->toString(
                                $to,
                                sprintf(
                                    '$%s(%s)',
                                    $functionName,
                                    $this->getterBuilder->createGetter($property)->toString($from)
                                )
                            )
                    );
                    break;
                case Property::COLLECTION:
                    $collectionVariableName = $this->createUniqueVariableName($blueprint);
                    $collectionItemVariableName = $this->createUniqueVariableName($blueprint);
                    $collectionOutputVariableName = $this->createUniqueVariableName($blueprint);
                    $functionBody .= sprintf(
                        '$%s=%s;%s',
                        $functionName,
                        $function->toString(
                            $from,
                            $to,
                            implode('', array_map(fn (Property $property) => $this->createSinglePropertyExpression($property, $from, $to), $property->blueprint->properties)),
                            $useStatements
                        ),
                        $this->loopBuilder->getLoop()->toString(
                            $collectionOutputVariableName,
                            $this->setterBuilder->createGetter($property)->toString($from),
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
                            $this->setterBuilder->createSetter($property)
                                ->toString(
                                    $to,
                                    sprintf(
                                        '$%s',
                                        $collectionOutputVariableName,
                                    )
                                )
                        ),
                    );
                    break;
                case Property::SIMPLE_OBJECT_COLLECTION:
                    $collectionVariableName = $this->createUniqueVariableName($blueprint);
                    $collectionItemVariableName = $this->createUniqueVariableName($blueprint);
                    $collectionOutputVariableName = $this->createUniqueVariableName($blueprint);
                    $functionBody .= sprintf(
                        '$%s=%s;%s',
                        $functionName,
                        $function->toString(
                            $from,
                            $to,
                            implode('', array_map(fn (Property $property) => $this->createSinglePropertyExpression($property, $from, $to), $property->blueprint->properties)),
                            $useStatements
                        ),
                        $this->loopBuilder->getLoop()->toString(
                            $collectionOutputVariableName,
                            $this->setterBuilder->createGetter($property)->toString($from),
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
                            $this->setterBuilder->createSetter($property)
                                ->toString(
                                    $to,
                                    sprintf(
                                        'new %s($%s)',
                                        $property->getClassType(),
                                        $collectionOutputVariableName,
                                    )
                                )
                        ),
                    );
                    break;
            }
        }

        return $function->toString(
            $sourceVariableName,
            $targetVariableName,
            $functionBody,
            $useStatements
        );
    }

    private static array $usedVariableNames = [];
    private function createUniqueVariableName(Blueprint $blueprint): string
    {
        do {
            $variableName = hash('crc32', $blueprint->reflection->getName() . self::$seed++, false);
        } while (in_array($variableName, self::$usedVariableNames));

        self::$usedVariableNames[] = $variableName = 'var_' . $variableName;

        return $variableName;
    }
}
