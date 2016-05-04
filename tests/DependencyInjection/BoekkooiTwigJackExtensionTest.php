<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\DependencyInjection;

use Boekkooi\Bundle\TwigJackBundle\DependencyInjection\BoekkooiTwigJackExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Bundle\AsseticBundle\DependencyInjection\AsseticExtension;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class BoekkooiTwigJackExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.debug', false);
        $this->container->setParameter('kernel.root_dir', sys_get_temp_dir() . '/BoekkooiTwigJackBundle/');
        $this->container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/BoekkooiTwigJackBundle/cache');
        $this->container->setParameter('kernel.bundles', array());
    }

    /**
     * @inheritdoc
     */
    protected function getContainerExtensions()
    {
        return array(
            new FrameworkExtension(),
            new AsseticExtension(),
            new BoekkooiTwigJackExtension()
        );
    }

    /**
     * @inheritdoc
     */
    protected function load(array $configurationValues = array())
    {
        $minimalConfiguration = $this->getMinimalConfiguration();

        foreach ($this->container->getExtensions() as $extension) {
            $extensionConfig = array(
                (isset($minimalConfiguration[$extension->getAlias()]) ? $minimalConfiguration[$extension->getAlias()] : array()),
                (isset($configurationValues[$extension->getAlias()]) ? $configurationValues[$extension->getAlias()] : array())
            );

            $extension->load($extensionConfig, $this->container);
        }
    }

    protected function getMinimalConfiguration()
    {
        return array(
            'framework' => array(
                'templating' => array(
                    'engine' => 'twig'
                )
            )
        );
    }

    public function testDefaults()
    {
        $this->load();

        $this->assertContainerBuilderHasService('boekkooi.twig_jack.defer.extension');
        $this->assertContainerBuilderNotHasService('boekkooi.twig_jack.constraint_validator');
        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.defer.prefix');

        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.exclamation', true);
    }

    public function testLoadEnabled()
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'defer' => array(
                    'enabled' => true,
                    'prefix' => 'foo_bar'
                ),
                'exclamation' => true
            )
        ));
        $this->assertContainerBuilderHasService('boekkooi.twig_jack.defer.extension');
        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.defer.prefix', 'foo_bar');

        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.exclamation', true);
    }

    public function testLoadDisabled()
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'defer' => false,
                'exclamation' => false
            )
        ));

        $this->assertContainerBuilderNotHasService('boekkooi.twig_jack.defer.extension');

        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.exclamation', false);
    }

    public function testLoadDeferTrue()
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'defer' => true,
                'exclamation' => false
            )
        ));

        $this->assertContainerBuilderHasService('boekkooi.twig_jack.defer.extension');
        $this->assertContainerBuilderHasParameter('boekkooi.twig_jack.defer.prefix');
    }

    /**
     * @dataProvider getLoadersDoctrine
     */
    public function testLoaderDoctrine($name, $options, $registryService)
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'loaders' => array(
                    $name => $options
                )
            )
        ));

        $service = 'boekkooi.twig_jack.loaders.' . $name;
        $serviceMan = $service . '.object_manager';
        $serviceRepo = $service . '.object_repository';

        // Check if services where created
        $this->assertContainerBuilderHasService($service);
        $this->assertContainerBuilderHasService($serviceMan);
        $this->assertContainerBuilderHasService($serviceRepo);

        // Check loader definition
        $loaderDef = $this->container->getDefinition($service);
        self::assertTrue($loaderDef->hasTag('twig.loader'));
        self::assertTrue($loaderDef->isPublic());
        self::assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(0));
        self::assertEquals($serviceRepo, (string) $loaderDef->getArgument(0));
        self::assertEquals($options['prefix'], $loaderDef->getArgument(1));
        self::assertNull($loaderDef->getArgument(2));

        // Check manager definition
        $managerDef = $this->container->getDefinition($serviceMan);
        self::assertEquals($options['model_class'], (string)$managerDef->getArgument(0));
        if (method_exists($managerDef, 'setFactory')) {
            self::assertEquals(
                array(new Reference($registryService), 'getManagerForClass'),
                $managerDef->getFactory()
            );
        } else {
            self::assertEquals($registryService, $managerDef->getFactoryService());
            self::assertEquals('getManagerForClass', $managerDef->getFactoryMethod());
        }

        // Check repo definition
        $repoDef = $this->container->getDefinition($serviceRepo);
        self::assertEquals($options['model_class'], (string) $repoDef->getArgument(0));
        if (method_exists($repoDef, 'setFactory')) {
            self::assertEquals(
                array(new Reference($serviceMan), 'getRepository'),
                $repoDef->getFactory()
            );
        } else {
            self::assertEquals($serviceMan, $repoDef->getFactoryService());
            self::assertEquals('getRepository', $repoDef->getFactoryMethod());
        }

        // We checked no lets make sure it compiles
        $this->container->setDefinition($registryService, new Definition('Doctrine\\Common\\Persistence\\ManagerRegistry'));
        $this->container->compile();
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
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'loaders' => array(
                    'custom' => array(
                        'prefix' => '',
                        'type' => 'custom',
                        'repository' => 'customRepo',
                        'model_class' => 'stdClass'
                    )
                )
            )
        ));

        $service = 'boekkooi.twig_jack.loaders.custom';
        $serviceMan = $service . '.object_manager';
        $serviceRepo = $service . '.object_repository';

        // Check if services where created
        $this->assertContainerBuilderHasService($service);
        $this->assertContainerBuilderNotHasService($serviceMan);
        $this->assertContainerBuilderNotHasService($serviceRepo);

        // Check loader definition
        $loaderDef = $this->container->getDefinition($service);
        self::assertTrue($loaderDef->hasTag('twig.loader'));
        self::assertTrue($loaderDef->isPublic());
        self::assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(0));
        self::assertEquals('customrepo', (string) $loaderDef->getArgument(0));
        self::assertEquals('', $loaderDef->getArgument(1));
        self::assertNull($loaderDef->getArgument(2));

        // We checked no lets make sure it compiles
        $this->container->setDefinition('customRepo', new Definition('Doctrine\\Common\\Persistence\\ObjectRepository'));
        $this->container->compile();
    }

    public function testLoaderLocale()
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'loaders' => array(
                    'custom' => array(
                        'prefix' => '',
                        'type' => 'orm',
                        'model_class' => 'stdClass',
                        'locale_callable' => 'x_y_z_callable'
                    )
                )
            )
        ));

        // Check loader definition
        $loaderDef = $this->container->getDefinition('boekkooi.twig_jack.loaders.custom');
        self::assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $loaderDef->getArgument(2));
        self::assertEquals('x_y_z_callable', (string) $loaderDef->getArgument(2));

        // We checked no lets make sure it compiles
        $this->container->setDefinition('customRepo', new Definition('Doctrine\\Common\\Persistence\\ObjectRepository'));
        $this->container->setDefinition('x_y_z_callable', new Definition('stdClass'));
        $this->container->compile();
    }

    /**
     * @dataProvider getLoadersInvalid
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoaderInvalid($options)
    {
        $this->load(array(
            'boekkooi_twig_jack' => array(
                'loaders' => array(
                    'invalid' => $options
                )
            )
        ));
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
        $this->load(array('boekkooi_twig_jack' => $options));

        $this->assertContainerBuilderHasService('boekkooi.twig_jack.constraint_validator');

        /** @var \Symfony\Component\DependencyInjection\Reference $envReference */
        $envReference = $this->container->getDefinition('boekkooi.twig_jack.constraint_validator')->getArgument(0);
        self::assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $envReference);
        self::assertEquals($envServiceName, (string) $envReference);
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
