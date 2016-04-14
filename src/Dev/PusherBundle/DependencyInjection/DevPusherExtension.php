<?php

namespace Dev\PusherBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DevPusherExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('dev_pusher.config', $config);
        $container->setParameter('dev_pusher.app_id', $config['app_id']);
        $container->setParameter('dev_pusher.app_key', $config['app_key']);
        $container->setParameter('dev_pusher.secret', $config['secret']);
        $container->setParameter('dev_pusher.options', $config['options']);
        $container->setParameter('dev_pusher.channel', $config['channel']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
