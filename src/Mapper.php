<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapper;

/**
 * The Mapper class is a simple wrapper around a string that contains a PHP code.
 */
class Mapper implements \Stringable
{
    public function __construct(
        private string $mappper
    ) {
    }

    public function map(mixed $data): mixed
    {
        $mapper = eval($this->toString());

        return $mapper($data);
    }

    public function __invoke(mixed $data): mixed
    {
        return $this->map($data);
    }

    public function toString(): string
    {
        return $this->mappper;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
