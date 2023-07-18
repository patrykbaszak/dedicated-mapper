<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

use PBaszak\MessengerMapperBundle\Contract\ModificatorInterface;
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

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function __construct(
        private Functions $functions,
        private array $modificators = [],
        public ?string $pathVariable = null,
        public string $pathVariableType = 'string',
        public ?string $useStatements = null,
        private string $source = 'data',
        private ?string $sourceType = null,
        private string $target = 'output',
        private ?string $targetType = null,
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

    public function build(Blueprint $source, Blueprint $target): self
    {
        $this->sourceBlueprint = $source;
        $this->targetBlueprint = $target;

        /* Modificators */
        foreach ($this->modificators as $modificator) {
            $modificator->modifyBlueprintExpression($this->sourceBlueprint, $this->targetBlueprint, $this);
        }

        return $this;
    }

    public function toString(): string
    {
        $expr = $this->functions->getFunction(
            (bool) $this->pathVariable,
            (bool) $this->useStatements,
            !empty($this->initialExpressions),
            !empty($this->finalExpressions),
        );

        do {
            $expr = str_replace(
                [
                    Functions::SOURCE_TYPE,
                    Functions::SOURCE_NAME,
                    Functions::TARGET_TYPE,
                    Functions::TARGET_NAME,
                    Functions::INITIAL_EXPRESSION,
                    Functions::EXPRESSIONS,
                    Functions::FINAL_EXPRESSION,
                    Functions::USE_STATEMENTS,
                    Functions::PATH_TYPE,
                    Functions::PATH_NAME,
                ],
                [
                    $this->sourceType,
                    $this->source,
                    $this->targetType,
                    $this->target,
                    implode("\n", array_map(fn (InitialExpression $expression) => $expression->toString(), $this->initialExpressions)),
                    implode("\n", array_map(fn (Expression $expression) => $expression->toString(), $this->expressions)),
                    implode("\n", array_map(fn (FinalExpression $expression) => $expression->toString(), $this->finalExpressions)),
                    $this->useStatements,
                    $this->pathVariableType,
                    $this->pathVariable,
                ],
                $expr
            );
        } while (false !== strpos($expr, '{{'));

        return $expr;
    }
}
