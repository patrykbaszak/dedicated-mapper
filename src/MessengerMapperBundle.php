<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Patryk Baszak <patryk.baszak@gmail.com>
 */
class MessengerMapperBundle extends Bundle
{
    public const ALIAS = 'messenger_mapper';

    public function getContainerExtension(): ExtensionInterface
    {
        return $this->extension ??= new DependencyInjection\MessengerMapperExtension();
    }
}
