<?php

namespace PBaszak\MessengerMapperBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const BUNDLES = [
        \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
        \PBaszak\MessengerCacheBundle\MessengerCacheBundle::class,
        \PBaszak\MessengerMapperBundle\MessengerMapperBundle::class,
        \JMS\SerializerBundle\JMSSerializerBundle::class,
    ];

    public function registerBundles(): iterable
    {
        foreach (self::BUNDLES as $bundle) {
            yield new $bundle();
        }
    }
}
