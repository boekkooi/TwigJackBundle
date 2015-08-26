<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node\Defer as DeferNode;
use Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference;
use Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser\Defer;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 * @covers Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser\Defer
 */
class DeferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Environment
     */
    protected $env;

    protected function setUp()
    {
        $this->env = new \Twig_Environment(
            new \Twig_Loader_Array([]),
            array('cache' => false, 'autoescape' => false, 'optimizations' => 0)
        );
    }

    /**
     * @dataProvider getTests
     */
    public function testCompile($source, \Twig_Node $bodyExpected, \Twig_Node $blocksExpected)
    {
        $result = $this->parse($source);

        $this->assertCount(1, $result->getNode('body'));
        $this->assertEquals($bodyExpected, $result->getNode('body')->getNode(0), "Nodes must be the same for:\n" . $source);

        $this->assertCount(count($blocksExpected), $result->getNode('blocks'));
        $this->assertEquals($blocksExpected, $result->getNode('blocks'));
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage Expected enddefer for defer 'def_jsx'
     */
    public function testNoEndBlock()
    {
        $this->parse('{% defer js "x" "foo" %}');
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage Expected enddefer for defer 'def_js356a192b7913b04c54574d18c28d46e6395428ab' (but css given)
     */
    public function testInvalidEndBlockName()
    {
        $this->parse('{% defer js %}X{% enddefer css %}');
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage expecting closing tag for the "defer" tag
     */
    public function testInvalidEndBlock()
    {
        $this->parse('{% defer js %}X{% endblock js %}');
    }

    public function getTests()
    {
        $defLine1 = 'def_js'.sha1(1);
        $defLine2 = 'def_js'.sha1(2);

        return array(
            array(<<<EOF
{% defer js %}X{% enddefer %}
EOF
                ,
                new DeferReference($defLine1, false, false, 'js', null, 1, 'defer'),
                new \Twig_Node(array(
                    $defLine1 => new \Twig_Node_Body(array(new DeferNode($defLine1, new \Twig_Node_Text('X', 1), 1)), array(), 1)
                ))
            ),
            array(<<<EOF
{% defer js %}X1{% enddefer %}
{% defer js %}X2{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                    new DeferReference($defLine1, false, false, 'js', null, 1, 'defer'),
                    new DeferReference($defLine2, false, false, 'js', null, 2, 'defer')
                ), array(), 1),
                new \Twig_Node(array(
                    $defLine1 => new \Twig_Node_Body(array(new DeferNode($defLine1, new \Twig_Node_Text('X1', 1), 1)), array(), 1),
                    $defLine2 => new \Twig_Node_Body(array(new DeferNode($defLine2, new \Twig_Node_Text('X2', 2), 2)), array(), 2)
                ))
            ),
            array(<<<EOF
{% defer js x %}VAR X{% enddefer %}
{% defer js y %}VAR Y{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                        new DeferReference($defLine1, 'x', false, 'js', null, 1, 'defer'),
                        new DeferReference($defLine2, 'y', false, 'js', null, 2, 'defer')
                    ), array(), 1),
                new \Twig_Node(array(
                        $defLine1 => new \Twig_Node_Body(array(new DeferNode($defLine1, new \Twig_Node_Text('VAR X', 1), 1)), array(), 1),
                        $defLine2 => new \Twig_Node_Body(array(new DeferNode($defLine2, new \Twig_Node_Text('VAR Y', 2), 2)), array(), 2)
                    ))
            ),
            array(<<<EOF
{% defer js 'x' %}UNIQUE X{% enddefer %}
{% defer js 'x' %}I should not be compiled{% enddefer %}
{% defer js 'y' %}UNIQUE Y{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                        new DeferReference('def_jsx', false, true, 'js', null, 1, 'defer'),
                        new DeferReference('def_jsy', false, true, 'js', null, 3, 'defer')
                    ), array(), 1),
                new \Twig_Node(array(
                        'def_jsx' => new \Twig_Node_Body(array(new DeferNode('def_jsx', new \Twig_Node_Text('UNIQUE X', 1), 1)), array(), 1),
                        'def_jsy' => new \Twig_Node_Body(array(new DeferNode('def_jsy', new \Twig_Node_Text('UNIQUE Y', 3), 3)), array(), 3)
                    ))
            ),
            array(<<<EOF
{% defer js 'x' 1 %}order 1{% enddefer %}
{% defer js 0 %}order 0{% enddefer %}
EOF
            ,
                new \Twig_Node(array(
                    new DeferReference('def_jsx', false, true, 'js', 1, 1, 'defer'),
                    new DeferReference($defLine2, false, false, 'js', 0, 2, 'defer')
                ), array(), 1),
                new \Twig_Node(array(
                    'def_jsx' => new \Twig_Node_Body(array(new DeferNode('def_jsx', new \Twig_Node_Text('order 1', 1), 1)), array(), 1),
                    $defLine2 => new \Twig_Node_Body(array(new DeferNode($defLine2, new \Twig_Node_Text('order 0', 2), 2)), array(), 2)
                ))
            )
        );
    }

    /**
     * @param $source
     * @return false|\Twig_Node_Module|\Twig_NodeInterface|void
     * @throws \Exception
     * @throws \Twig_Error_Syntax
     */
    private function parse($source)
    {
        $env = $this->env;
        $env->addTokenParser(new Defer('def_'));
        $stream = $env->tokenize($source);
        $parser = new \Twig_Parser($env);

        $result = $parser->parse($stream);

        return $result;
    }
}
