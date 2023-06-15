<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Mapper
{
    private string $mappper;

    public function map(mixed $data, ?ValidatorInterface $validator = null): mixed
    {
        $mapper = eval($this->toString());

        return $mapper($data);
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
