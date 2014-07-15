<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Node;

use Twig_Compiler;
use Twig_Node_BlockReference;

/**
 * Represents a defer injection call node.
 *
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferReference extends Twig_Node_BlockReference
{
    public function __construct($name, $reference, $lineno, $tag = null)
    {
        parent::__construct($name, $lineno, $tag);

        $this->setAttribute('reference', $reference);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $name = $this->getAttribute('name');
        $reference = $this->getAttribute('reference');

        $compiler
            ->addDebugInfo($this)
            ->write("\$this->env->getExtension('defer')->cache('{$reference}', '{$name}', \$this->renderBlock('{$name}', \$context, \$blocks));\n")
        ;
    }
}
