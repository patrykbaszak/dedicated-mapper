<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Contract\MapperInterface;
use PBaszak\DedicatedMapper\Contract\NormalizerInterface;
use PBaszak\DedicatedMapper\Expression\Builder\AnonymousObjectExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassObjectExpressionBuilder;

class Normalizer implements NormalizerInterface
{
    public function __construct(
        protected MapperInterface $mapper
    ) {}

    /**
     * {@inheritdoc}
     */
    public function normalize(
        object $data, 
        string $type = 'array', 
        ?Context $context = null
    ): array|object {
        return $this->mapper->map(
            $data, 
            get_class($data),
            new ReflectionClassObjectExpressionBuilder(),
            match ($type) {
                'object' => new AnonymousObjectExpressionBuilder(),
                'array' => new ArrayExpressionBuilder(),
                default => throw new \InvalidArgumentException('Invalid type. Allowed types: `object`, `array`.'),
            },
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(
        object|array $data, 
        string $blueprint, 
        ?Context $context = null
    ): array|object {
        return $this->mapper->map(
            $data, 
            $blueprint,
            match (gettype($data)) {
                'object' => new AnonymousObjectExpressionBuilder(),
                'array' => new ArrayExpressionBuilder(),
                default => throw new \InvalidArgumentException('Invalid $data type. Allowed types: `object`, `array`. Given: `' . gettype($data) .'`.'),
            },
            new ReflectionClassObjectExpressionBuilder(),
            $context
        );
    }
}
