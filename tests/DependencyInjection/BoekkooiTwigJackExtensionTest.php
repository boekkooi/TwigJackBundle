<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

use Boekkooi\Bundle\TwigJackBundle\DependencyInjection\BoekkooiTwigJackExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();

        $loader = new BoekkooiTwigJackExtension();
        $loader->load(array('boekkooi_twig_jack' => array()), $container);

        $this->assertTrue($container->has('boekkooi.twigjack.defer.extension'));
        $this->assertTrue($container->hasParameter('boekkooi.twigjack.defer.prefix'));
        $this->assertTrue($container->hasParameter('templating.name_parser.class'));
        $this->assertEquals('Boekkooi\Bundle\TwigJackBundle\Templating\TemplateNameParser', $container->getParameter('templating.name_parser.class'));
    }

    public function testLoadEnabled()
    {
        $container = new ContainerBuilder();

        $loader = new BoekkooiTwigJackExtension();
        $loader->load(array('boekkooi_twig_jack' => array(
                'defer' => array(
                    'enabled' => true,
                    'prefix' => 'foo_bar'
                ),
                'exclamation' => true
            )),
            $container
        );
        $this->assertTrue($container->has('boekkooi.twigjack.defer.extension'));
        $this->assertTrue($container->hasParameter('boekkooi.twigjack.defer.prefix'));
        $this->assertEquals('foo_bar', $container->getParameter('boekkooi.twigjack.defer.prefix'));

        $this->assertTrue($container->hasParameter('templating.name_parser.class'));
        $this->assertEquals('Boekkooi\Bundle\TwigJackBundle\Templating\TemplateNameParser', $container->getParameter('templating.name_parser.class'));
    }

    public function testLoadDisabled()
    {
        $container = new ContainerBuilder();
        $loader = new BoekkooiTwigJackExtension();
        $loader->load(array('boekkooi_twig_jack' => array(
                'defer' => false,
                'exclamation' => false
            )),
            $container
        );

        $this->assertFalse($container->has('boekkooi.twigjack.defer.extension'));
        $this->assertFalse($container->hasParameter('templating.name_parser.class'));
    }

    public function testLoadDeferTrue()
    {
        $container = new ContainerBuilder();
        $loader = new BoekkooiTwigJackExtension();
        $loader->load(array('boekkooi_twig_jack' => array(
                'defer' => true,
                'exclamation' => false
            )),
            $container
        );

        $this->assertTrue($container->has('boekkooi.twigjack.defer.extension'));
        $this->assertTrue($container->hasParameter('boekkooi.twigjack.defer.prefix'));
    }


}
