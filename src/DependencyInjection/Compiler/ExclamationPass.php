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
            !$container->hasDefinition('templating.cache_warmer.template_paths') ||
            !$container->hasParameter('boekkooi.twig_jack.exclamation') ||
            !$container->getParameter('boekkooi.twig_jack.exclamation')
        ) {
            return;
        }

        $container->getDefinition('templating.cache_warmer.template_paths')
            ->addArgument(new Reference('kernel'));
    }
}
