<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO\Properties;

use PBaszak\MessengerMapperBundle\Attribute\Accessor;
use PBaszak\MessengerMapperBundle\Attribute\MappingCallback;
use PBaszak\MessengerMapperBundle\Attribute\TargetProperty;

class Mapper
{
    /**
     * @param MappingCallback[] $mappingCallbacks
     */
    public function __construct(
        public ?Accessor $accessor = null,
        public ?TargetProperty $targetProperty = null,
        public array $mappingCallbacks = [],
    ) {
    }
}
