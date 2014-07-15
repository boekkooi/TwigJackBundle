<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\Node;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node\Defer;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferTest extends \Twig_Test_NodeTestCase
{
    /**
     * @covers Twig_Node_BlockReference::__construct
     */
    public function testConstructor()
    {
        $bodyNode = new \Twig_Node_Body();
        $node = new Defer('foo', $bodyNode, 1);

        $this->assertEquals('foo', $node->getAttribute('name'));
        $this->assertEquals($bodyNode, $node->getNode('body'));
    }

    /**
     * @covers Twig_Node_BlockReference::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    public function getTests()
    {
        return array(
            array(new Defer('foo', new \Twig_Node_Body(), 1), <<<EOF
// line 1
public function block_foo(\$context, array \$blocks = array())
{
}
EOF
            ),
        );
    }
}
