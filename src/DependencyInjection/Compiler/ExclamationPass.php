<?php
namespace Boekkooi\Bundle\TwigJackBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ExclamationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasParameter('boekkooi.twig_jack.exclamation') ||
            !$container->getParameter('boekkooi.twig_jack.exclamation')
        ) {
            return;
        }

        # This will fail when templating is off
        # http://symfony.com/doc/current/book/templating.html#template-configuration
        $container->getDefinition('templating.name_parser')
            ->setClass('%templating.name_parser.class%');

        $container->getDefinition('templating.cache_warmer.template_paths')
            ->setClass('%templating.cache_warmer.template_paths.class%')
            ->addArgument(new Reference('kernel'));

        // Patch assetic service when availible
        if ($container->hasDefinition('assetic.twig_formula_loader')) {
            $container->getDefinition('assetic.twig_formula_loader')
                ->setClass('%assetic.twig_formula_loader.class%');
        }
    }
}
