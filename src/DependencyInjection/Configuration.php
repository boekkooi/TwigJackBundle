<?php
namespace Boekkooi\src\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('boekkooi_twig_jack');
        $rootNode
            ->children()
                ->booleanNode('defer')->defaultTrue()->end()
                ->booleanNode('exclamation')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
