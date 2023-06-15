<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

use PBaszak\MessengerMapperBundle\DTO\Property;

class PropertiesMatcher implements PropertiesMatcherInterface
{
    /**
     * @param Property[] $sourceProperties
     * @param Property[] $targetProperties
     */
    public function matchProperties(array &$sourceProperties, array &$targetProperties): void
    {
        do {
            [$beforeSourceCount, $beforeTargetCount] = [$this->countMirrors($sourceProperties), $this->countMirrors($targetProperties)];
            $this->doMatchProperties($sourceProperties, $targetProperties);
            [$afterSourceCount, $afterTargetCount] = [$this->countMirrors($sourceProperties), $this->countMirrors($targetProperties)];
        } while (
            $beforeSourceCount !== $afterSourceCount ||
            $beforeTargetCount !== $afterTargetCount ||
            $afterSourceCount !== $afterTargetCount
        );
    }

    /**
     * @param Property[] $properties
     *
     * @return int number of properties with mirror properties
     */
    private function countMirrors(array $properties): int
    {
        $count = 0;
        foreach ($properties as $property) {
            if ($property->hasMirrorProperty()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param Property[] $source
     * @param Property[] $destination
     */
    private function doMatchProperties(array &$source, array &$destination): void
    {
        foreach ($source as $property) {
            if ($property->hasMirrorProperty()) {
                continue;
            }

            $this->setOrCreateMatchedProperties($property, $destination);
        }

        foreach ($destination as $property) {
            if ($property->hasMirrorProperty()) {
                continue;
            }

            $this->setOrCreateMatchedProperties($property, $source);
        }
    }

    /**
     * @param Property[] $mirrors
     */
    private function setOrCreateMatchedProperties(Property $property, array &$mirrors): void
    {
        $name = $property->getName();
        foreach ($mirrors as $mirror) {
            $mirrorOrigin ??= $mirror->origin;
            if ($mirror->hasMirrorProperty()) {
                continue;
            }

            if ($name === $mirror->getName()) {
                $property->setMirrorProperty($mirror);

                return;
            }
        }

        if ($property->parent && !$property->parent->hasMirrorProperty()) {
            return;
        }

        $mirrorOrigin ??= $property->origin ^ Property::SOURCE ^ Property::TARGET;

        $property->setMirrorProperty(
            new Property(
                $mirrorOrigin,
                $property->serializer?->serializedName?->getSerializedName()
                    ?? $property->mapper?->targetProperty?->name
                    ?? $property->getName(),
                $property->type,
                $property->isNullable,
                $property->isCollectionItem,
                $property->collectionType,
                $property->parent ? $property->parent->getMirrorProperty() : null,
            )
        );

        $mirrors[] = $property->getMirrorProperty();
    }
}
