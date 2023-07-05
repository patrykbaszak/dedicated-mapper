<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator;

interface ModificatorInterface
{
    /** @return Callback[] */
    public function getModificators(): array;
}
