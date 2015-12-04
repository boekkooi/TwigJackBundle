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

        self::assertEquals('foo', $node->getAttribute('name'));
        self::assertEquals('bar', $node->getAttribute('reference'));
        self::assertEquals(false, $node->getAttribute('unique'));
        self::assertEquals(false, $node->getAttribute('offset'));
        self::assertEquals('my_var', $node->getAttribute('variable'));
    }

    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null, $isPattern = false)
    {
        parent::testCompile($node, $source, $environment, $isPattern);
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
