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
        $node = new DeferReference('foo', 'my_var', false, 'bar', 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
        $this->assertEquals('bar', $node->getAttribute('reference'));
        $this->assertEquals(false, $node->getAttribute('unique'));
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
            array(new DeferReference('foo', false, false, 'js', 1), <<<EOF
// line 1
\$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks));
EOF
            ),
            array(new DeferReference('foo', false, true, 'js', 1), <<<EOF
// line 1
if (!\$this->env->getExtension('defer')->contains('js', 'foo')) {
    \$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks), 'foo');
}
EOF
            ),
            array(new DeferReference('foo', 'a_var', true, 'js', 1), <<<EOF
// line 1
if (!\$this->env->getExtension('defer')->contains('js', 'foo|' . \$context['a_var'])) {
    \$this->env->getExtension('defer')->cache('js', \$this->renderBlock('foo', \$context, \$blocks), 'foo|' . \$context['a_var']);
}
EOF
            ),
        );
    }
}
