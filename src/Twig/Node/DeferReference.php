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
    /**
     * @param string $name
     * @param string|false $variable
     * @param boolean $unique
     * @param string $reference
     * @param integer $lineno The line number
     * @param string $tag The tag name associated with the Node
     */
    public function __construct($name, $variable, $unique, $reference, $lineno, $tag = null)
    {
        parent::__construct($name, $lineno, $tag);

        $this->setAttribute('reference', $reference);
        $this->setAttribute('unique', $unique);
        $this->setAttribute('variable', $variable);
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
        $variable = $this->getAttribute('variable');

        if ($variable) {
            $compiler
                ->addDebugInfo($this)
                ->write("if (!\$this->env->getExtension('defer')->contains('{$reference}', '$name|' . \$context['{$variable}'])) {\n")
                ->indent()
                    ->write("\$this->env->getExtension('defer')->cache('{$reference}', \$this->renderBlock('{$name}', \$context, \$blocks), '$name|' . \$context['{$variable}']);\n")
                ->outdent()
                ->write("}\n")
            ;
            return;
        }
        if ($this->getAttribute('unique')) {
            $compiler
                ->addDebugInfo($this)
                ->write("if (!\$this->env->getExtension('defer')->contains('{$reference}', '{$name}')) {\n")
                ->indent()
                    ->write("\$this->env->getExtension('defer')->cache('{$reference}', \$this->renderBlock('{$name}', \$context, \$blocks), '{$name}');\n")
                ->outdent()
                ->write("}\n")
            ;
            return;
        }
        $compiler
            ->addDebugInfo($this)
            ->write("\$this->env->getExtension('defer')->cache('{$reference}', \$this->renderBlock('{$name}', \$context, \$blocks));\n")
        ;
    }
}
