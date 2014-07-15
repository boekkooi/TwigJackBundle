<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Node;

use Twig_Compiler;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Defer extends \Twig_Node_Block
{
    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');

        $compiler
            ->addDebugInfo($this)
            ->write("public function block_{$name}(\$context, array \$blocks = array())\n", "{\n")
            ->indent()
                ->subcompile($this->getNode('body'))
            ->outdent()
            ->write("}\n\n")
        ;
    }

}
