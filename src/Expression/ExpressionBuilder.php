<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Contract\FunctionInterface;
use PBaszak\MessengerMapperBundle\Contract\GetterInterface;
use PBaszak\MessengerMapperBundle\Contract\LoopInterface;
use PBaszak\MessengerMapperBundle\Contract\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Contract\SetterInterface;
use PBaszak\MessengerMapperBundle\Expression\Assets\Expression;
use PBaszak\MessengerMapperBundle\Expression\Builder\AbstractBuilder;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;
use PBaszak\MessengerMapperBundle\Properties\Property;

class ExpressionBuilder
{
    /**
     * @var ModificatorInterface[]
     */
    protected array $modificators = [];
    protected Blueprint $source;
    protected Blueprint $target;
    protected bool $throwExceptionOnMissingProperty = false;

    public function __construct(
        protected Blueprint $blueprint,
        protected AbstractBuilder&GetterInterface $getterBuilder,
        protected AbstractBuilder&SetterInterface $setterBuilder,
        protected FunctionInterface $functionBuilder,
        protected LoopInterface $loopBuilder,
        protected ?array $groups = null,
    ) {
        $this->source = $getterBuilder->getBlueprint($blueprint->isCollection) ?? $blueprint;
        $this->target = $setterBuilder->getBlueprint($blueprint->isCollection) ?? $blueprint;
    }

    protected function newPropertyExpression(Property $source, Property $target): Expression
    {
        $expression = new Expression(
            $this->getterBuilder->getGetter($source),
            $this->setterBuilder->getSetter($target),
            $this->modificators,
            [], // this Expression Builder does not include own callbacks
            $this->throwExceptionOnMissingProperty,
        );

        return $expression->build($source, $target);
    }

    protected function newFunctionExpression(array $expressions): FunctionExpression
    {
        $expression = new FunctionExpression(
            $this->functionBuilder->getFunction(),
            $this->modificators,
            [], // this Expression Builder does not include own callbacks
            $this->throwExceptionOnMissingProperty,
        );

        return $expression->build($source, $target);
    }
}
