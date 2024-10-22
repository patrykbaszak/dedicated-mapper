<?php

namespace PBaszak\DedicatedMapper\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const BUNDLES = [
        \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
        \PBaszak\DedicatedMapper\DedicatedMapperBundle::class,
        \JMS\SerializerBundle\JMSSerializerBundle::class,
    ];

    public function registerBundles(): iterable
    {
        foreach (self::BUNDLES as $bundle) {
            yield new $bundle();
        }
    }
}
