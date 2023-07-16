<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Assets;

class Modificator
{
    public function __construct(
        private string $expression,
        public int $priority = 0,
    ) {}
}
