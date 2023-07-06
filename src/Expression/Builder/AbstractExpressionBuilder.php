<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Builder;

use PBaszak\MessengerMapperBundle\Expression\InitialExpression;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;
use PBaszak\MessengerMapperBundle\Properties\Blueprint;

abstract class AbstractExpressionBuilder
{
    /** @var string[] */
    protected static $initialExpressionIds = [];

    /**
     * @param ModificatorInterface[] $modificators
     */
    public function __construct(
        protected array $modificators = []
    ) {
    }

    public function getSourceType(Blueprint $blueprint): string
    {
        return 'mixed';
    }

    public function getOutputType(Blueprint $blueprint): ?string
    {
        return null;
    }

    public function getGetterInitialExpression(Blueprint $blueprint, string $initialExpressionId): InitialExpression
    {
        return new InitialExpression('');
    }
}
