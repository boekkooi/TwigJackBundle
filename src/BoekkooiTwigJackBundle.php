<?php
namespace Boekkooi\Bundle\TwigJackBundle;

use Boekkooi\Bundle\TwigJackBundle\DependencyInjection\Compiler\ExclamationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExclamationPass());
    }
}
