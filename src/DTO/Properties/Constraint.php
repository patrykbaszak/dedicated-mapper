<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO\Properties;

class Constraint
{
    public function __construct(
        public readonly string $className,
        public readonly array $arguments = [],
    ) {
    }
}
