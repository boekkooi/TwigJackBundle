<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node\DeferReference;
use Boekkooi\Bundle\TwigJackBundle\Twig\Node\Defer as DeferNode;
use Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser\Defer;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 * @covers Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser\Defer
 */
class DeferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTests
     */
    public function testCompile($source, \Twig_Node $bodyExpected, \Twig_Node $blocksExpected)
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addTokenParser(new Defer('def_'));
        $stream = $env->tokenize($source);
        $parser = new \Twig_Parser($env);

        $result = $parser->parse($stream);

        $this->assertCount(1, $result->getNode('body'));
        $this->assertEquals($bodyExpected, $result->getNode('body')->getNode(0));

        $this->assertCount(count($blocksExpected), $result->getNode('blocks'));
        $this->assertEquals($blocksExpected, $result->getNode('blocks'));
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage Expected enddefer for defer 'def_jsx'
     */
    public function testNoEndBlock()
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addTokenParser(new Defer('def_'));
        $stream = $env->tokenize('{% defer js "x" "foo" %}');
        $parser = new \Twig_Parser($env);

        $parser->parse($stream);
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage Expected enddefer for defer 'def_js0' (but css given)
     */
    public function testInvalidEndBlockName()
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addTokenParser(new Defer('def_'));
        $stream = $env->tokenize('{% defer js %}X{% enddefer css %}');
        $parser = new \Twig_Parser($env);

        $parser->parse($stream);
    }

    /**
     * @expectedException \Twig_Error_Syntax
     * @expectedExceptionMessage expecting closing tag for the "defer" tag
     */
    public function testInvalidEndBlock()
    {
        $env = new \Twig_Environment(new \Twig_Loader_String(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addTokenParser(new Defer('def_'));
        $stream = $env->tokenize('{% defer js %}X{% endblock js %}');
        $parser = new \Twig_Parser($env);

        $parser->parse($stream);
    }

    public function getTests()
    {
        return array(
            array(<<<EOF
{% defer js %}X{% enddefer %}
EOF
                ,
                new DeferReference('def_js0', false, false, 'js', 1, 'defer'),
                new \Twig_Node(array(
                    'def_js0' => new \Twig_Node_Body(array(new DeferNode('def_js0', new \Twig_Node_Text('X', 1), 1)), array(), 1)
                ))
            ),
            array(<<<EOF
{% defer js %}X1{% enddefer %}
{% defer js %}X2{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                    new DeferReference('def_js0', false, false, 'js', 1, 'defer'),
                    new DeferReference('def_js1', false, false, 'js', 2, 'defer')
                ), array(), 1),
                new \Twig_Node(array(
                    'def_js0' => new \Twig_Node_Body(array(new DeferNode('def_js0', new \Twig_Node_Text('X1', 1), 1)), array(), 1),
                    'def_js1' => new \Twig_Node_Body(array(new DeferNode('def_js1', new \Twig_Node_Text('X2', 2), 2)), array(), 2)
                ))
            ),
            array(<<<EOF
{% defer js x %}VAR X{% enddefer %}
{% defer js y %}VAR Y{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                        new DeferReference('def_js0', 'x', false, 'js', 1, 'defer'),
                        new DeferReference('def_js1', 'y', false, 'js', 2, 'defer')
                    ), array(), 1),
                new \Twig_Node(array(
                        'def_js0' => new \Twig_Node_Body(array(new DeferNode('def_js0', new \Twig_Node_Text('VAR X', 1), 1)), array(), 1),
                        'def_js1' => new \Twig_Node_Body(array(new DeferNode('def_js1', new \Twig_Node_Text('VAR Y', 2), 2)), array(), 2)
                    ))
            ),
            array(<<<EOF
{% defer js 'x' %}UNIQUE X{% enddefer %}
{% defer js 'x' %}I should not be compiled{% enddefer %}
{% defer js 'y' %}UNIQUE Y{% enddefer %}
EOF
                ,
                new \Twig_Node(array(
                        new DeferReference('def_jsx', false, true, 'js', 1, 'defer'),
                        new DeferReference('def_jsy', false, true, 'js', 3, 'defer')
                    ), array(), 1),
                new \Twig_Node(array(
                        'def_jsx' => new \Twig_Node_Body(array(new DeferNode('def_jsx', new \Twig_Node_Text('UNIQUE X', 1), 1)), array(), 1),
                        'def_jsy' => new \Twig_Node_Body(array(new DeferNode('def_jsy', new \Twig_Node_Text('UNIQUE Y', 3), 3)), array(), 3)
                    ))
            )
        );
    }
}
