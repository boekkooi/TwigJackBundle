<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\Loader;

use Boekkooi\Bundle\TwigJackBundle\Twig\Loader\DoctrineLoader;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DoctrineLoaderTest extends \PHPUnit_Framework_TestCase
{
    const TEMPLATE_INTERFACE = 'Boekkooi\\Bundle\\TwigJackBundle\\Model\\TemplateInterface';
    const TRANSLATABLE_TEMPLATE_INTERFACE = 'Boekkooi\\Bundle\\TwigJackBundle\\Model\\TranslatableTemplateInterface';

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorInvalidCallable()
    {
        new DoctrineLoader($this->getMockRepository(), 'prefix::', new \stdClass());
    }

    /**
     * @dataProvider getInvalidPrefixMethods
     * @expectedException \Twig_Error_Loader
     * @expectedExceptionMessage Malformed namespaced
     */
    public function testInvalidPrefix($method, $arguments)
    {
        $loader = new DoctrineLoader($this->getMockRepository(), 'prefix::');
        call_user_func_array(array($loader, $method), $arguments);
    }

    public function getInvalidPrefixMethods()
    {
        return array(
            array('getCacheKey', array('')),
            array('getCacheKey', array('')),
            array('getCacheKey', array('invalid_prefix::template')),
            array('getSource', array('invalid_prefix::template')),
            array('isFresh', array('invalid_prefix::template', 1272509157)),
        );
    }

    /**
     * @dataProvider getValidCalls
     * @expectedException \Twig_Error_Loader
     * @expectedExceptionMessage Unable to find template
     */
    public function testNoTemplateFound($templateId, $method, $arguments)
    {
        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')->with($templateId)->willReturn(null);

        $loader = new DoctrineLoader($repo, 'prefix::');
        call_user_func_array(array($loader, $method), $arguments);
    }

    /**
     * @dataProvider getValidCalls
     * @expectedException \Twig_Error_Loader
     * @expectedExceptionMessage Unexpected template type
     */
    public function testEnforceInterface($templateId, $method, $arguments)
    {
        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')->with($templateId)->willReturn(new \stdClass());

        $loader = new DoctrineLoader($repo, 'prefix::');
        call_user_func_array(array($loader, $method), $arguments);
    }

    public function getValidCalls()
    {
        return array(
            array('template0', 'getCacheKey', array('prefix::template0')),
            array('template1', 'getSource', array('prefix::template1')),
            array('template2', 'isFresh', array('prefix::template2', 1272509157)),
        );
    }

    public function testSource()
    {
        $source = 'Good new template';

        $template = $this->getMock(self::TEMPLATE_INTERFACE);
        $template->expects($this->atLeastOnce())->method('getTemplate')
            ->willReturn($source);

        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')
            ->with('templateName')->willReturn($template);

        $loader = new DoctrineLoader($repo, '');
        $this->assertEquals($source, $loader->getSource('templateName'));
    }

    /**
     * @dataProvider getIsFreshCalls
     */
    public function testIsFresh(\DateTime $datetime1, \DateTime $datetime2, $expect)
    {
        $template = $this->getMock(self::TEMPLATE_INTERFACE);
        $template->expects($this->atLeastOnce())->method('getLastModified')
            ->willReturn($datetime1);

        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')
            ->with('templateName')->willReturn($template);

        $loader = new DoctrineLoader($repo);
        $this->assertEquals($expect, $loader->isFresh('database::templateName', $datetime2->getTimeStamp()));
    }

    public function getIsFreshCalls()
    {
        $interval = new \DateInterval('PT3H');
        $now = new \DateTime();

        $future = new \DateTime();
        $future->add($interval);

        return array(
            array($now, $now, true),
            array($now, $future, true),
            array($future, $now, false),
        );
    }

    public function testCacheKey()
    {
        $template = $this->getMock(self::TEMPLATE_INTERFACE);
        $template->expects($this->atLeastOnce())->method('getIdentifier')
            ->willReturn('identifier');

        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')
            ->with('templateName')->willReturn($template);

        $loader = new DoctrineLoader($repo);
        $this->assertEquals('database::|identifier', $loader->getCacheKey('database::templateName'));
    }

    /**
     * @dataProvider getCacheKeyTranslation
     */
    public function testCacheKeyTranslation($locale, $callbackLocale, $key)
    {
        $template = $this->getMock(self::TRANSLATABLE_TEMPLATE_INTERFACE);
        $template->expects($this->atLeastOnce())->method('getIdentifier')
            ->willReturn('identifier');
        $template->expects($this->atLeastOnce())->method('setCurrentLocale')
            ->with($locale);
        $template->expects($this->atLeastOnce())->method('getCurrentLocale')
            ->willReturn($locale);

        $repo = $this->getMockRepository();
        $repo->expects($this->once())->method('find')
            ->with('templateName')->willReturn($template);

        $loader = new DoctrineLoader($repo, 'x::', function() use ($callbackLocale) { return $callbackLocale; });
        $this->assertEquals($key, $loader->getCacheKey('x::templateName'));
    }

    public function getCacheKeyTranslation()
    {
        return array(
            array('nl', 'nl', 'x::|nl|identifier'),
            array('en', false, 'x::|en|identifier')
        );
    }

    protected function getMockRepository()
    {
        return $this->getMock('Doctrine\\Common\\Persistence\\ObjectRepository');
    }
}