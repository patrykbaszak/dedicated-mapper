<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Service;

interface PropertiesMatcherInterface
{
    /**
     * @param Property[] $sourceProperties
     * @param Property[] $targetProperties
     */
    public function matchProperties(array &$sourceProperties, array &$targetProperties): void;
}
