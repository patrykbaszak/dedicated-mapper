<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

use PBaszak\MessengerMapperBundle\DTO\Property;

interface PropertiesExtractorInterface
{
    /**
     * @param int                $origin           Property::SOURCE or Property::TARGET
     * @param class-string       $class
     * @param array<string>|null $serializerGroups
     * @param array<string>|null $validatorGroups
     *
     * @return Property[]
     */
    public function extractProperties(
        int $origin,
        string $class,
        ?array $serializerGroups,
        ?array $validatorGroups,
        ?Property $parent = null
    ): array;
}
