<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

use PBaszak\DedicatedMapper\Contract\EncoderInterface;
use PBaszak\DedicatedMapper\Contract\MapperInterface;
use PBaszak\DedicatedMapper\Contract\NormalizerInterface;
use PBaszak\DedicatedMapper\Contract\SerializerInterface;
use PBaszak\DedicatedMapper\Expression\Builder\AnonymousObjectExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ArrayExpressionBuilder;
use PBaszak\DedicatedMapper\Expression\Builder\ReflectionClassObjectExpressionBuilder;

class Serializer implements SerializerInterface
{
    public function __construct(
        protected EncoderInterface $encoder,
        protected ?MapperInterface $mapper = null,
        protected ?NormalizerInterface $normalizer = null,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function serialize(
        array|object $data, 
        string $format, 
        ?Context $context = null
    ): string {
        $type = 'array';
        $normalizedData = $this->normalizer?->normalize($data, $type, $context) ?? $data;
        return $this->encoder->encode($normalizedData, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(
        string $data, 
        string $blueprint, 
        string $format = 'json', 
        string $type = 'class_object', 
        ?Context $context = null
    ): array|object {
        $decodedData = $this->encoder->decode($data, $format, 'array');
        return $this->mapper->map(
            $decodedData, 
            $blueprint,
            new ArrayExpressionBuilder(),
            match ($type) {
                'class_object' => new ReflectionClassObjectExpressionBuilder(),
                'object' => new AnonymousObjectExpressionBuilder(),
                'array' => new ArrayExpressionBuilder(),
                default => throw new \InvalidArgumentException('Invalid type. Allowed types: `class_object`, `object`, `array`.'),
            },
            $context
        );
    }
}
