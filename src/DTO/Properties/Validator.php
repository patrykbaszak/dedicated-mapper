<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO\Properties;

class Validator
{
    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        public array $constraints
    ) {}
}
