<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\Node;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferReferenceTest extends \Twig_Test_NodeTestCase
{
    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference::__construct
     */
    public function testConstructor()
    {
        $node = new DeferReference('foo', 'my_var', false, 'bar', null, 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
        $this->assertEquals('bar', $node->getAttribute('reference'));
        $this->assertEquals(false, $node->getAttribute('unique'));
        $this->assertEquals(false, $node->getAttribute('offset'));
        $this->assertEquals('my_var', $node->getAttribute('variable'));
    }

    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        return array(
            array(new DeferReference('foo', false, false, 'js', null, 1), <<<EOF
// line 1
\$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks), null, null);
EOF
            ),
            array(new DeferReference('foo', false, true, 'js', 1, 1), <<<EOF
// line 1
if (!\$this->env->getExtension('defer')->contains('js', 'foo')) {
    \$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks), 'foo', 1);
}
EOF
            ),
            array(new DeferReference('foo', 'a_var', true, 'js', null, 1), <<<EOF
// line 1
if (!\$this->env->getExtension('defer')->contains('js', 'foo|' . \$context['a_var'])) {
    \$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks), 'foo|' . \$context['a_var'], null);
}
EOF
            ),
        );
    }
}
