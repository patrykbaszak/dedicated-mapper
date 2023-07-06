<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Contract;

use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;

interface AbstractExpressionInterface
{
    /** @return ModificatorInterface[] */
    public function getModificators(): array;
}
