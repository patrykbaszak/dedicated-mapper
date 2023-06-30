<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Mapper;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

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
        protected string $originVariableName = 'data',
        protected string $targetVariableName = 'output',
    ) {}

    public function createExpression()
    {
        $expression = '';
        foreach ($this->blueprint->properties as $propertyName => $property) {
            if ($property->blueprint) {
                $function = $this->functionBuilder->createFunction($property->blueprint);
                $expression .= $function->toString(

                );
            }
            if ($function && $property->blueprint->isCollection) {
                $loop = $this->loopBuilder->createLoop();
            }
        }
    }

    public function getMapper(): Mapper
    {
        return $this->mapper;
    }
}

class Expression
{
    public ?string $inputVariableName = null;
    public ?string $outputVariableName = null;

    public ?Loop $loop = null;
    public ?Function_ $function = null;
    public ?Getter $getter = null;
    public ?Setter $setter = null;

    public function toString(): string
    {
        return 
    }
}
