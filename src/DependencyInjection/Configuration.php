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
        $rootNode->append($this->loadLoadersNode());
        $rootNode->append($this->loadDeferNode());
        $rootNode->append($this->loadConstraintNode());
        $rootNode
            ->children()
                ->booleanNode('exclamation')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }

    private function loadLoadersNode()
    {
        $treeBuilder = new TreeBuilder();

        $node = $treeBuilder->root('loaders');
        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('prefix')->isRequired()->treatNullLike('')->treatFalseLike('')->end()
                    ->enumNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->defaultValue('orm')
                        ->values(array('orm', 'mongo', 'couch', 'custom'))
                    ->end()
                    ->scalarNode('repository')
                        ->info('Custom service that must return a Doctrine\Common\Persistence\ObjectRepository')
                    ->end()
                    ->scalarNode('model_class')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('locale_callable')
                        ->defaultValue(null)
                        ->info('A service that returns the current locale that will be used by a template.')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function loadDeferNode()
    {
        $treeBuilder = new TreeBuilder();

        $node = $treeBuilder->root('defer');
        $node
            ->addDefaultsIfNotSet()
            ->treatNullLike(array())
            ->treatFalseLike(array('enabled' => false))
            ->treatTrueLike(array('enabled' => true))
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('prefix')->defaultValue('_defer_ref_')->end()
            ->end();

        return $node;
    }

    private function loadConstraintNode()
    {
        $treeBuilder = new TreeBuilder();

        $node = $treeBuilder->root('constraint');
        $node
            ->addDefaultsIfNotSet()
            ->treatNullLike(array())
            ->treatFalseLike(array('enabled' => false))
            ->treatTrueLike(array('enabled' => true))
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->scalarNode('environment')->defaultValue('twig')->end()
            ->end();

        return $node;
    }
}
