<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\Expression\Modificator;

use PBaszak\MessengerMapperBundle\Contract\AbstractExpressionInterface;

interface ModificatorInterface
{
    public function getPriority(): int;

    public function setBuilder(AbstractExpressionInterface $builder): void;
}
