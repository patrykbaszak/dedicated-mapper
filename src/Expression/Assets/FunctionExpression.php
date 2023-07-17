<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression;

use PBaszak\MessengerMapperBundle\Expression\Assets\FinalExpression;
use PBaszak\MessengerMapperBundle\Expression\Assets\Functions;
use PBaszak\MessengerMapperBundle\Expression\Assets\InitialExpression;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

class FunctionExpression
{
    private Blueprint $sourceBlueprint;
    private Blueprint $targetBlueprint;

    /** @var InitialExpression[] */
    private array $initialExpressions = [];
    /** @var Expression[] */
    private array $expressions = [];
    /** @var FinalExpression[] */
    private array $finalExpressions = [];

    public function __construct(
        private Functions $functions,
        private array $modificators = [],
        public bool $usePathVariable = false,
        public bool $useUseStatements = false,
    ) {
    }

    public function addInitialExpression(InitialExpression $expression): self
    {
        $this->initialExpressions[] = $expression;

        return $this;
    }

    public function addExpression(Expression $expression): self
    {
        $this->expressions[] = $expression;

        return $this;
    }

    public function addFinalExpression(FinalExpression $expression): self
    {
        $this->finalExpressions[] = $expression;

        return $this;
    }

    public function build(Blueprint $source, Blueprint $target): void
    {
        $this->sourceBlueprint = $source;
        $this->targetBlueprint = $target;

        /** Modificators */
        foreach ($this->modificators as $modificator) {
            $modificator->modifyBlueprintExpression($this->sourceBlueprint, $this->targetBlueprint, $this);
        }
    }

    public function toString(): string
    {
        
    }
}
