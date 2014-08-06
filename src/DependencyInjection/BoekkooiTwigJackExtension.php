<?php
namespace Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
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

        $this->loadDefer($container, $loader, $config);
        $this->loadConstraint($container, $loader, $config);

        if ($config['exclamation']) {
            $loader->load('exclamation.yml');
        }

        $this->loadLoaders($container, $loader, $config);
    }

    private function loadLoaders(ContainerBuilder $container, LoaderInterface $configLoader, array $config)
    {
        if (empty($config['loaders'])) {
            return;
        }

        $configLoader->load('loader.yml');

        foreach ($config['loaders'] as $name => $loader) {
            $this->setupLoader($container, $name, $loader);
        }
    }

    private function setupLoader(ContainerBuilder $container, $name, array $loaderConfig)
    {
        $repositoryService = $this->createLoaderRepository($container, $name, $loaderConfig);

        // Create loader
        $loader = $container->setDefinition(sprintf('boekkooi.twig_jack.loaders.%s', $name), new DefinitionDecorator('boekkooi.twig_jack.loader.abstract'));
        $loader->setPublic(true);
        $loader->addTag('twig.loader');

        $loader
            ->replaceArgument(0, new Reference($repositoryService))
            ->replaceArgument(1, (string)$loaderConfig['prefix'])
            ->replaceArgument(2, !empty($loaderConfig['locale_callable']) ? new Reference($loaderConfig['locale_callable']) : null);
    }

    private function createLoaderRepository(ContainerBuilder $container, $loaderName, array $loaderConfig)
    {
        switch ($loaderConfig['type']){
            case 'custom':
                if (empty($loaderConfig['repository'])) {
                    throw new InvalidConfigurationException(sprintf('No repository option provided for %s', $loaderName));
                }
                return $loaderConfig['repository'];
            case 'orm':
                $managerService = 'doctrine';
                break;
            case 'mongo':
                $managerService = 'doctrine_mongodb';
                break;
            case 'couch':
                $managerService = 'doctrine_couchdb';
                break;
            // @codeCoverageIgnoreStart
            default:
                throw new InvalidConfigurationException(sprintf('Unknown loader type provided for %s', $loaderName));
            // @codeCoverageIgnoreEnd
        }

        $repositoryService = sprintf('boekkooi.twig_jack.loaders.%s.object_repository', $loaderName);
        $entityManagerService = sprintf('boekkooi.twig_jack.loaders.%s.object_manager', $loaderName);

        $modelClass = ltrim($loaderConfig['model_class'], '\\');

        // Create factory to get the entity manager for the entity
        $container
            ->setDefinition($entityManagerService, new DefinitionDecorator('boekkooi.twig_jack.doctrine.object_manager.abstract'))
            ->setPublic(true)
            ->setFactoryService($managerService)
            ->setArguments(array($modelClass));

        // Create factory to get the repository for the entity
        $container
            ->setDefinition($repositoryService, new DefinitionDecorator('boekkooi.twig_jack.doctrine.object_repository.abstract'))
            ->setFactoryService(new Reference($entityManagerService))
            ->setArguments(array($modelClass));

        return $repositoryService;
    }

    private function loadDefer(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if (!$config['defer']['enabled']) {
            return;
        }

        $container->setParameter('boekkooi.twig_jack.defer.prefix', $config['defer']['prefix']);
        $loader->load('defer.yml');
    }

    private function loadConstraint(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if (!$config['constraint']['enabled']) {
            return;
        }

        $loader->load('constraint.yml');
        $container->getDefinition('boekkooi.twig_jack.constraint_validator')
            ->replaceArgument(0, new Reference($config['constraint']['environment']));
    }
}
