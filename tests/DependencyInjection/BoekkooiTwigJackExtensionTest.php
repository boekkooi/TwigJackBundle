<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

use Boekkooi\Bundle\TwigJackBundle\DependencyInjection\BoekkooiTwigJackExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BoekkooiTwigJackExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new BoekkooiTwigJackExtension();
    }

    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testLoad()
    {
        $container = new ContainerBuilder();

        $this->extension->load(array('boekkooi_twig_jack' => array()), $container);

        $this->assertTrue($container->has('boekkooi.twig_jack.defer.extension'));
        $this->assertFalse($container->has('boekkooi.twig_jack.constraint_validator'));
        $this->assertTrue($container->hasParameter('boekkooi.twig_jack.defer.prefix'));
        $this->assertTrue($container->hasParameter('templating.name_parser.class'));
        $this->assertEquals('Boekkooi\Bundle\TwigJackBundle\Templating\TemplateNameParser', $container->getParameter('templating.name_parser.class'));
    }

    public function testLoadEnabled()
    {
        $container = new ContainerBuilder();

        $this->extension->load(array('boekkooi_twig_jack' => array(
                'defer' => array(
                    'enabled' => true,
                    'prefix' => 'foo_bar'
                ),
                'exclamation' => true
            )),
            $container
        );
        $this->assertTrue($container->has('boekkooi.twig_jack.defer.extension'));
        $this->assertTrue($container->hasParameter('boekkooi.twig_jack.defer.prefix'));
        $this->assertEquals('foo_bar', $container->getParameter('boekkooi.twig_jack.defer.prefix'));

        $this->assertTrue($container->hasParameter('templating.name_parser.class'));
        $this->assertEquals('Boekkooi\Bundle\TwigJackBundle\Templating\TemplateNameParser', $container->getParameter('templating.name_parser.class'));
    }

    public function testLoadDisabled()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_jack' => array(
                'defer' => false,
                'exclamation' => false
            )),
            $container
        );

        $this->assertFalse($container->has('boekkooi.twig_jack.defer.extension'));
        $this->assertFalse($container->hasParameter('templating.name_parser.class'));
    }

    public function testLoadDeferTrue()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_jack' => array(
                'defer' => true,
                'exclamation' => false
            )),
            $container
        );

        $this->assertTrue($container->has('boekkooi.twig_jack.defer.extension'));
        $this->assertTrue($container->hasParameter('boekkooi.twig_jack.defer.prefix'));
    }

    /**
     * @dataProvider getLoadersDoctrine
     */
    public function testLoaderDoctrine($name, $options, $registryService)
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_doctrine_template' => array(
            'loaders' => array(
                $name => $options
            )
        )), $container);

        $service = 'boekkooi.twig_jack.loaders.' . $name;
        $serviceMan = $service . '.object_manager';
        $serviceRepo = $service . '.object_repository';

        // Check if services where created
        $this->assertTrue($container->has($service));
        $this->assertTrue($container->has($serviceMan));
        $this->assertTrue($container->has($serviceRepo));

        // Check loader definition
        $loaderDef = $container->getDefinition($service);
        $this->assertTrue($loaderDef->hasTag('twig.loader'));
        $this->assertTrue($loaderDef->isPublic());
        $this->assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(0));
        $this->assertEquals($serviceRepo, (string) $loaderDef->getArgument(0));
        $this->assertEquals($options['prefix'], $loaderDef->getArgument(1));
        $this->assertNull($loaderDef->getArgument(2));

        // Check manager definition
        $repoDef = $container->getDefinition($serviceMan);
        $this->assertEquals($registryService, $repoDef->getFactoryService());
        $this->assertEquals($options['model_class'], (string) $repoDef->getArgument(0));

        // Check repo definition
        $repoDef = $container->getDefinition($serviceRepo);
        $this->assertEquals($options['model_class'], (string) $repoDef->getArgument(0));

        // We checked no lets make sure it compiles
        $container->setDefinition($registryService, new Definition('Doctrine\\Common\\Persistence\\ManagerRegistry'));
        $container->compile();
    }

    public function getLoadersDoctrine()
    {
        return array(
            array(
                'my',
                array(
                    'prefix' => 'orm://',
                    'type' => 'orm',
                    'model_class' => 'stdClass'
                ),
                'doctrine'
            ),
            array(
                'sexy',
                array(
                    'prefix' => 'mongo://',
                    'type' => 'mongo',
                    'model_class' => 'stdClass'
                ),
                'doctrine_mongodb'
            ),
            array(
                'loader',
                array(
                    'prefix' => 'couch://',
                    'type' => 'couch',
                    'model_class' => 'stdClass'
                ),
                'doctrine_couchdb'
            )
        );
    }

    public function testLoaderCustom()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_doctrine_template' => array(
            'loaders' => array(
                'custom' => array(
                    'prefix' => '',
                    'type' => 'custom',
                    'repository' => 'customRepo',
                    'model_class' => 'stdClass'
                )
            )
        )), $container);

        $service = 'boekkooi.twig_jack.loaders.custom';
        $serviceMan = $service . '.object_manager';
        $serviceRepo = $service . '.object_repository';

        // Check if services where created
        $this->assertTrue($container->has($service));
        $this->assertFalse($container->has($serviceMan));
        $this->assertFalse($container->has($serviceRepo));

        // Check loader definition
        $loaderDef = $container->getDefinition($service);
        $this->assertTrue($loaderDef->hasTag('twig.loader'));
        $this->assertTrue($loaderDef->isPublic());
        $this->assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(0));
        $this->assertEquals('customrepo', (string) $loaderDef->getArgument(0));
        $this->assertEquals('', $loaderDef->getArgument(1));
        $this->assertNull($loaderDef->getArgument(2));

        // We checked no lets make sure it compiles
        $container->setDefinition('customRepo', new Definition('Doctrine\\Common\\Persistence\\ObjectRepository'));
        $container->compile();
    }

    public function testLoaderLocale()
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_doctrine_template' => array(
            'loaders' => array(
                'custom' => array(
                    'prefix' => '',
                    'type' => 'orm',
                    'model_class' => 'stdClass',
                    'locale_callable' => 'x_y_z_callable'
                )
            )
        )), $container);

        // Check loader definition
        $loaderDef = $container->getDefinition('boekkooi.twig_jack.loaders.custom');
        $this->assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(2));
        $this->assertEquals('x_y_z_callable', (string) $loaderDef->getArgument(2));

        // We checked no lets make sure it compiles
        $container->setDefinition('customRepo', new Definition('Doctrine\\Common\\Persistence\\ObjectRepository'));
        $container->setDefinition('x_y_z_callable', new Definition('stdClass'));
        $container->compile();
    }

    /**
     * @dataProvider getLoadersInvalid
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoaderInvalid($options)
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_doctrine_template' => array(
            'loaders' => array(
                'invalid' => $options
            )
        )), $container);
    }

    public function getLoadersInvalid()
    {
        return array(
            // Missing prefix
            array(array(
                'type' => 'orm',
                'model_class' => 'stdClass'
            )),
            // Invalid type
            array(array(
                'prefix' => '',
                'type' => 'xxx',
                'model_class' => 'stdClass'
            )),
            // Missing model_class
            array(array(
                'prefix' => '',
                'type' => 'orm'
            )),
            // Missing repo
            array(array(
                'prefix' => '',
                'type' => 'custom',
                'model_class' => 'stdClass'
            ))
        );
    }

    /**
     * @dataProvider getLoadConstraint
     */
    public function testLoadConstraint($options, $envServiceName)
    {
        $container = new ContainerBuilder();
        $this->extension->load(array('boekkooi_twig_jack' => $options), $container);

        $this->assertTrue($container->has('boekkooi.twig_jack.constraint_validator'));

        /** @var \Symfony\Component\DependencyInjection\Reference $envReference */
        $envReference = $container->getDefinition('boekkooi.twig_jack.constraint_validator')->getArgument(0);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $envReference);
        $this->assertEquals($envServiceName, (string) $envReference);
    }

    public function getLoadConstraint()
    {
        return array(
            array(
                array('constraint' => true),
                'twig'
            ),
            array(
                array('constraint' => array('enabled' => true, 'environment' => 'new_twig')),
                'new_twig'
            )
        );
    }
}
