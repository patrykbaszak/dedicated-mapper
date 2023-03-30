<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DTO;

class Property
{
    public function __construct(
        public ?string $fromName = null,
        public ?string $toName = null,
        public ?string $fromType = null,
        public ?string $toType = null,
        public ?string $fromGetter = null,
        public ?string $toSetter = null,
        public array $callbacks = [],
    ) {}
}
