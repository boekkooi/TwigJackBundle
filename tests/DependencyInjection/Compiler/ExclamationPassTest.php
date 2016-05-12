<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\DependencyInjection\Compiler;

use Boekkooi\Bundle\TwigJackBundle\DependencyInjection\Compiler\ExclamationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExclamationPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @inheritdoc
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExclamationPass());
    }

    /**
     * @test
     */
    public function it_should_change_the_templating_services_when_enabled()
    {
        $this->setDefinition('templating.name_parser', new Definition(__CLASS__));
        $this->setDefinition('templating.cache_warmer.template_paths', new Definition(__CLASS__, array('finder', 'locator')));

        $this->setParameter('boekkooi.twig_jack.exclamation', true);

        $this->setParameter('templating.name_parser.class', 'DummyNameParser');
        $this->setParameter('templating.cache_warmer.template_paths.class', 'DummyTemplatePaths');
        $this->setParameter('assetic.twig_formula_loader.class', 'DummyAssetic');

        $this->compile();

        $this->assertContainerBuilderHasService('templating.name_parser', 'DummyNameParser');
        $this->assertContainerBuilderHasService('templating.cache_warmer.template_paths', 'DummyTemplatePaths');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('templating.cache_warmer.template_paths', 2, new Reference('kernel'));
        $this->assertContainerBuilderNotHasService('assetic.twig_formula_loader');
    }

    /**
     * @test
     */
    public function it_should_change_the_assetic_service_when_enabled()
    {
        $this->setDefinition('templating.name_parser', new Definition(__CLASS__));
        $this->setDefinition('templating.cache_warmer.template_paths', new Definition(__CLASS__));
        $this->setDefinition('assetic.twig_formula_loader.real', new Definition(__CLASS__));
        $this->setDefinition('assetic.twig_formula_loader', new Definition(__CLASS__));

        $this->setParameter('boekkooi.twig_jack.exclamation', true);

        $this->setParameter('templating.name_parser.class', 'DummyNameParser');
        $this->setParameter('templating.cache_warmer.template_paths.class', 'DummyTemplatePaths');
        $this->setParameter('assetic.twig_formula_loader.class', 'DummyAssetic');

        $this->compile();

        $this->assertContainerBuilderHasService('assetic.twig_formula_loader.real', 'DummyAssetic');
        $this->assertContainerBuilderHasService('assetic.twig_formula_loader', __CLASS__);
    }

    /**
     * @test
     */
    public function it_should_change_the_assetic_service_when_enabled_fallback()
    {
        $this->setDefinition('templating.name_parser', new Definition(__CLASS__));
        $this->setDefinition('templating.cache_warmer.template_paths', new Definition(__CLASS__));
        $this->setDefinition('assetic.twig_formula_loader', new Definition(__CLASS__));

        $this->setParameter('boekkooi.twig_jack.exclamation', true);

        $this->setParameter('templating.name_parser.class', 'DummyNameParser');
        $this->setParameter('templating.cache_warmer.template_paths.class', 'DummyTemplatePaths');
        $this->setParameter('assetic.twig_formula_loader.class', 'DummyAssetic');

        $this->compile();

        $this->assertContainerBuilderHasService('assetic.twig_formula_loader', 'DummyAssetic');
        $this->assertContainerBuilderNotHasService('assetic.twig_formula_loader.real');
    }

    /**
     * @test
     */
    public function it_should_not_change_templating_services_when_not_enabled()
    {
        $this->setDefinition('templating.name_parser', new Definition(__CLASS__));
        $this->setParameter('boekkooi.twig_jack.exclamation', false);

        $this->compile();

        $this->assertContainerBuilderHasService('templating.name_parser', __CLASS__);
    }
}
