<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Templating;

use Boekkooi\Bundle\TwigJackBundle\Templating\TemplateNameParser;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\TemplateNameParserTest as ParentTemplateNameParserTest;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference as SymfonyTemplateReference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TemplateNameParserTest extends ParentTemplateNameParserTest
{
    /**
     * @var TemplateNameParser
     */
    protected $parser;

    protected function setUp()
    {
        // Bundle list mocking
        $mockOneBundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $mockOneBundle->expects($this->any())
            ->method('getPath')
            ->willReturn('/root/one');
        $mockTwoBundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $mockTwoBundle->expects($this->any())
            ->method('getPath')
            ->willReturn('/root/two');
        $bundles = array(
            'OneLevelBundle' => array(
                true,
                $mockOneBundle
            ),
            'TwoLevelBundle' => array(
                true,
                true,
                $mockTwoBundle
            )
        );

        // Based on the original
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(function ($bundle) use ($bundles) {
                if (in_array($bundle, array('SensioFooBundle', 'SensioCmsFooBundle', 'FooBundle'))) {
                    return true;
                }
                if (array_key_exists($bundle, $bundles)) {
                    return $bundles[$bundle];
                }
                throw new \InvalidArgumentException();
            }))
        ;
        $this->parser = new TemplateNameParser($kernel);
    }

    public function testSkipTemplateReferenceInterfaceParse()
    {
        $template = new TemplateReference('ref.php', 'php');
        $this->assertEquals($template, $this->parser->parse($template));
    }

    /**
     * @dataProvider getExclamationToTemplateProvider
     */
    public function testExclamationParse($name, TemplateReferenceInterface $ref = null)
    {
        $template = $this->parser->parse($name);

        $this->assertEquals($ref->getPath(), $template->getPath());
        $this->assertEquals($ref->getLogicalName(), $template->getLogicalName());

        // Test cache
        $this->assertEquals($template, $this->parser->parse($name));
    }

    public function getExclamationToTemplateProvider()
    {
        return array(
            array('!OneLevelBundle:Post:index.html.php', new TemplateReference('/root/one/Resources/views/Post/index.html.php', 'php')),
            array('!TwoLevelBundle:Post:index.html.php', new TemplateReference('/root/two/Resources/views/Post/index.html.php', 'php')),
            array('!::layout.html.twig', new SymfonyTemplateReference('', '', 'layout', 'html', 'twig'))
        );
    }
}
