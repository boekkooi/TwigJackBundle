<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression\DeferReference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferReferenceTest extends \Twig_Test_NodeTestCase
{
    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression\DeferReference::__construct
     */
    public function testConstructor()
    {
        $valueNode = new \Twig_Node_Expression_Constant('js', 1);
        $node = new DeferReference('defer', new \Twig_Node(array($valueNode)), 1);

        self::assertEquals($valueNode, $node->getNode('name'));
    }

    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression\DeferReference::__construct
     *
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage Only one argument is allowed for "defer".
     */
    public function testConstructorMultipleNodes()
    {
        $valueNode = new \Twig_Node_Expression_Constant('js', 1);
        new DeferReference('defer', new \Twig_Node(array($valueNode, $valueNode)), 1);
    }

    /**
     * @covers Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression\DeferReference::compile
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null, $isPattern = false)
    {
        parent::testCompile($node, $source, $environment, $isPattern);
    }

    public function getTests()
    {
        $deferWithOutput = new DeferReference('defer', new \Twig_Node(array(new \Twig_Node_Expression_Constant('js', 1))), 1);
        $deferWithOutput->setAttribute('output', true);

        $deferAsString = new DeferReference('defer', new \Twig_Node(array(new \Twig_Node_Expression_Constant('js', 1))), 1);
        $deferAsString->setAttribute('as_string', true);

        return array(
            array(new DeferReference('defer', new \Twig_Node(array(new \Twig_Node_Expression_Constant('js', 1))), 1), <<<EOF
implode('', \$this->env->getExtension('defer')->retrieve("js"))
EOF
            ),
            array($deferAsString, <<<EOF
(string) implode('', \$this->env->getExtension('defer')->retrieve("js"))
EOF
            ),
            array($deferWithOutput, <<<EOF
// line 1
\$_defer_block_references = \$this->env->getExtension('defer')->retrieve("js");
foreach (\$_defer_block_references as \$_defer_block_reference) {
    echo \$_defer_block_reference;
}
unset(\$_defer_block_references,\$_defer_block_reference);
EOF
            )
        );
    }
}
