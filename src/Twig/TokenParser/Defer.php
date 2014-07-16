<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

use Twig_Error_Syntax;
use Twig_Node;
use Twig_Node_Print;
use Twig_Token;
use Twig_TokenParser;
use Boekkooi\Bundle\TwigJackBundle\Twig\Node;

/**
 * Marks a section of a template as being usable in a later stage.
 *
 * <pre>
 *  {% defer javascript %}
 *    {% javascripts 'my.js' %}
 *      <script src="{{ asset_url }}"></script>
 *    {% endjavascripts %}
 *  {% enddefer %}
 * </pre>
 *
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Defer extends Twig_TokenParser
{
    private static $i = 0;

    protected $blockPrefix;

    public function __construct($blockPrefix)
    {
        $this->blockPrefix = $blockPrefix;
    }

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_Token A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $reference = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $name = $stream->nextIf(\Twig_Token::NAME_TYPE);
        $unique = $name !== null;
        if ($unique) {
            $name = $this->blockPrefix . $name->getValue();
            if ($this->parser->hasBlock($name)) {
                return null;
            }
        } else {
            do {
                $name = $this->blockPrefix . $reference . (self::$i++);
            } while ($this->parser->hasBlock($name));
        }

        $this->parser->setBlock($name, $block = new Node\Defer($name, new Twig_Node(array()), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($stream->nextIf(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(Twig_Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new Twig_Error_Syntax(sprintf("Expected enddefer for defer '$name' (but %s given)", $value), $stream->getCurrent()->getLine(), $stream->getFilename());
                }
            }
        } else {
            $body = new Twig_Node(array(
                new Twig_Node_Print($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ));
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new Node\DeferReference($name, $unique, $reference, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('enddefer');
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'defer';
    }
}
