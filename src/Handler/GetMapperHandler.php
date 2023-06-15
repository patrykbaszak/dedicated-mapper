<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Handler;

use PBaszak\MessengerMapperBundle\Contract\GetMapper;
use PBaszak\MessengerMapperBundle\DTO\Property;
use PBaszak\MessengerMapperBundle\Service\ExpressionBuilderInterface;
use PBaszak\MessengerMapperBundle\Service\PropertiesExtractorInterface;
use PBaszak\MessengerMapperBundle\Service\PropertiesMatcherInterface;
use PBaszak\MessengerMapperBundle\Service\TypeServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class GetMapperHandler
{
    public function __construct(
        private TypeServiceInterface $typeService,
        private PropertiesExtractorInterface $propertiesExtractor,
        private PropertiesMatcherInterface $propertiesMatcher,
        private ExpressionBuilderInterface $expressionBuilder,
    ) {
    }

    public function __invoke(GetMapper $query): string
    {
        // from what type to what type mapping
        $sourceType = $this->typeService->calculateType($query->from, $query->fromType);
        $targetType = $this->typeService->calculateType($query->to, $query->toType);
        // extract list of all source and target properties
        $sourceProperties = class_exists($query->from, false) ?
            $this->propertiesExtractor->extractProperties(
                Property::SOURCE,
                $query->from,
                $query->useSerializer ? $query->serializerGroups : null,
                $query->useValidator ? $query->validatorGroups : null
            ) : [];
        $targetProperties = class_exists($query->to, false) ?
            $this->propertiesExtractor->extractProperties(
                Property::TARGET,
                $query->to,
                $query->useSerializer ? $query->serializerGroups : null,
                $query->useValidator ? $query->validatorGroups : null
            ) : [];
        // mirror source and target properties - create it if not exists
        $this->propertiesMatcher->matchProperties($sourceProperties, $targetProperties);
        // create abstract root property
        $reducedTargetProperties = $this->reduceProperties($targetProperties);
        // get expression
        $expression = $this->expressionBuilder->buildExpression(
            reset($reducedTargetProperties),
            'data',
            $sourceType,
            $targetType,
            $this->getMapSeparator($query->fromType),
            $this->getMapSeparator($query->toType),
        );

        return $query->useValidator ?
            sprintf($query::MAPPER_TEMPLATE_WITH_VALIDATOR, $expression) :
            sprintf($query::MAPPER_TEMPLATE, $expression);
    }

    /**
     * @param Property[] $properties
     *
     * @return Property[]
     */
    private function reduceProperties(array $properties): array
    {
        foreach ($properties as $index => $property) {
            if ($property->parent) {
                unset($properties[$index]);
            }
        }

        return $properties;
    }

    private function getMapSeparator(?string $type): ?string
    {
        if ($type) {
            preg_match('/^map\{(?<separator>.+)\}$/', $type, $matches);
        }

        return $matches['separator'] ?? null;
    }
}
