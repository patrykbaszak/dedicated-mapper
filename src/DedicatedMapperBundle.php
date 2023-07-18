<?php

declare(strict_types=1);

namespace PBaszak\DedicatedMapperBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Patryk Baszak <patryk.baszak@gmail.com>
 */
class DedicatedMapperBundle extends Bundle
{
    public const ALIAS = 'dedicated_mapper';

    public function getContainerExtension(): ExtensionInterface
    {
        return $this->extension ??= new DependencyInjection\DedicatedMapperExtension();
    }
}
