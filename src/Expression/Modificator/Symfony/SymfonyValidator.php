<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator\Symfony;

use PBaszak\MessengerMapperBundle\Contract\AbstractExpressionInterface;
use PBaszak\MessengerMapperBundle\Expression\Modificator\ModificatorInterface;

class SymfonyValidator implements ModificatorInterface
{
    public function getPriority(): int
    {
        return 0;
    }

    public function setBuilder(AbstractExpressionInterface $builder): void
    {
    }
}
