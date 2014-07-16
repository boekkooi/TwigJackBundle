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
    public function __construct($name, $unique, $reference, $lineno, $tag = null)
    {
        parent::__construct($name, $lineno, $tag);

        $this->setAttribute('reference', $reference);
        $this->setAttribute('unique', $unique);
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

        if ($this->getAttribute('unique')) {
            $compiler
                ->addDebugInfo($this)
                ->write("if (!\$this->env->getExtension('defer')->contains('{$reference}', '{$name}')) {\n")
                ->indent()
                    ->write("\$this->env->getExtension('defer')->cache('{$reference}', '{$name}', \$this->renderBlock('{$name}', \$context, \$blocks));\n")
                ->outdent()
                ->write("}\n")
            ;
        } else {
            $compiler
                ->addDebugInfo($this)
                ->write("\$this->env->getExtension('defer')->cache('{$reference}', '{$name}', \$this->renderBlock('{$name}', \$context, \$blocks));\n")
            ;
        }
    }
}
