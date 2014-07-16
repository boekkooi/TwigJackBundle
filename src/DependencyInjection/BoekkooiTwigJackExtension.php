<?php
namespace Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $config);

        if ($config['defer']) {
            $loader->load('defer.yml');
        }

        if ($config['exclamation']) {
            $loader->load('exclamation.yml');
        }
    }
}
