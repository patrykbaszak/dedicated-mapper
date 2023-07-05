<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator;

class Callback
{
    /** @var callable $callback */
    public $callback;

    public function __construct(
        callable $callback,
        public readonly int $priority = 0,
    ) {
        $this->callback = $callback;
    }
}
