<?php

declare(strict_types=1);

namespace PBaszak\MessengerMapperBundle\DependencyInjection;

use PBaszak\MessengerMapperBundle\MessengerMapperBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MessengerMapperExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        // do nothing
    }

    public function getAlias(): string
    {
        return MessengerMapperBundle::ALIAS;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('packages/messenger_cache.yaml');
        $loader->load('services.yaml');
    }
}
