<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

use PBaszak\MessengerMapperBundle\DTO\Property;

interface ExpressionBuilderInterface
{
    public function buildExpression(
        Property $targetProperty,
        string $sourceVariableName,
        int $sourceType,
        int $targetType,
        ?string $sourceMapSeparator,
        ?string $targetMapSeparator
    ): string;
}
