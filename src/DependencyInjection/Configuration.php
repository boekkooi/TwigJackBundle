<?php
namespace Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

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
                ->arrayNode('defer')
                    ->treatNullLike(array())
                    ->treatFalseLike(array('enabled' => false))
                    ->treatTrueLike(array('enabled' => true))
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('prefix')->defaultValue('_defer_ref_')->end()
                    ->end()
                ->end()
                ->booleanNode('exclamation')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
