<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Properties;

use ReflectionClass;

class Blueprint
{
    public function __construct(
        public ReflectionClass $reflection,
        public ?string $originVariableName = null,
        public ?string $targetVariableName = null,
    ) {}
}
