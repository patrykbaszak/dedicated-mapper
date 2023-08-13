<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper\Expression\Assets;

use PBaszak\DedicatedMapper\Contract\ModificatorInterface;
use PBaszak\DedicatedMapper\Properties\Blueprint;
use PBaszak\DedicatedMapper\Utils\HasNotFilledPlaceholdersTrait;

class FunctionExpression
{
    use HasNotFilledPlaceholdersTrait;

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

        $args = [
            Functions::SOURCE_TYPE => $this->sourceType,
            Functions::SOURCE_NAME => $this->source,
            Functions::TARGET_TYPE => $this->targetType,
            Functions::TARGET_NAME => $this->target,
            Functions::INITIAL_EXPRESSION => implode("\n", array_map(fn (InitialExpression $expression) => $expression->toString(), $this->initialExpressions)),
            Functions::EXPRESSIONS => implode("\n", array_map(fn (Expression $expression) => $expression->toString(), $this->expressions)),
            Functions::FINAL_EXPRESSION => implode("\n", array_map(fn (FinalExpression $expression) => $expression->toString(), $this->finalExpressions)),
            Functions::USE_STATEMENTS => $this->useStatements,
            Functions::PATH_TYPE => $this->pathVariableType,
            Functions::PATH_NAME => $this->pathVariable,
        ];

        do {
            $expr = str_replace(array_keys($args), array_values($args), $expr);
        } while ($this->hasNotFilledPlaceholders(array_keys($args), $expr));

        if (in_array($expr, self::$createdExpressions, true)) {
            $counts = 0;
            foreach (self::$createdExpressions as $createdExpression) {
                if ($createdExpression === $expr) {
                    ++$counts;
                }
            }

            if ($counts > 3) {
                throw new \LogicException('Expression creation loop detected.');
            }
        }
        self::$createdExpressions[] = $expr;

        return $expr;
    }

    /** @var string[] */
    public static array $createdExpressions = [];
}
