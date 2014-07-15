<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression;

use Twig_Compiler;
use Twig_Error_Syntax;
use Twig_Node;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferReference extends \Twig_Node_Expression_BlockReference
{
    public function __construct($name, Twig_Node $args, $line, $tag = null)
    {
        if ($args->count() !== 1) {
            throw new Twig_Error_Syntax(sprintf('Only one argument is allowed for "%s".', $name));
        }
        parent::__construct($args->nodes[0], false, $line, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        if ($this->getAttribute('as_string')) {
            $compiler->raw('(string) ');
        }

        if ($this->getAttribute('output')) {
            $compiler
                ->write("\$_defer_block_references = \$this->env->getExtension('defer')->retrieve(")
                    ->subcompile($this->getNode('name'))
                    ->raw(");\n")
                ->write("foreach (\$_defer_block_references as \$_defer_block_reference) {\n")
                ->indent()
                    ->write("echo \$_defer_block_reference;\n")
                ->outdent()
                ->write("}\n")
                ->write("unset(\$_defer_block_references,\$_defer_block_reference);\n")
            ;
        } else {
            $compiler
                ->addDebugInfo($this)
                ->raw("implode('', array_map(\n")
                ->indent()
                    ->write("\\Closure::bind(function (\$defer_block_reference) use (\$context, \$blocks) {\n")
                    ->indent()
                        ->write("return \$defer_block_reference;\n")
                    ->outdent()
                    ->write("}, \$this),\n")
                    ->write("\$this->env->getExtension('defer')->retrieve(")
                        ->subcompile($this->getNode('name'))
                        ->raw(")\n")
                ->outdent()
                ->write("))")
            ;
        }
    }
}
