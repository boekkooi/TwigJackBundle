<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

use Boekkooi\Bundle\TwigJackBundle\Twig\Node;
use Twig_Error_Syntax;
use Twig_Node;
use Twig_Token;
use Twig_TokenParser;
use Twig_TokenStream;

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
    protected $blockPrefix;

    /**
     * @param string $blockPrefix
     */
    public function __construct($blockPrefix)
    {
        $this->blockPrefix = $blockPrefix;
    }

    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     * @return null|Twig_Token A Twig_NodeInterface instance
     */
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $reference = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $name = $stream->nextIf(\Twig_Token::STRING_TYPE);
        $name = $name !== null ? $name->getValue() : false;

        $unique = $name !== false;

        $variableName = $stream->nextIf(\Twig_Token::NAME_TYPE);
        $variableName = $variableName !== null ? $variableName->getValue() : false;

        $offset = $stream->nextIf(\Twig_Token::NUMBER_TYPE);
        $offset = $offset !== null ? $offset->getValue() : false;

        if ($name) {
            $name = $this->blockPrefix . $reference . $name;
            if ($this->parser->hasBlock($name)) {
                $this->bodyParse($stream, $name);

                return null;
            }
        } else {
            $name = $this->createUniqueBlockName($token, $reference);
        }

        $this->parser->setBlock($name, $block = new Node\Defer($name, new Twig_Node(array()), $lineno));
        $this->parser->pushLocalScope();

        $body = $this->bodyParse($stream, $name);

        $block->setNode('body', $body);
        $this->parser->popLocalScope();

        return new Node\DeferReference($name, $variableName, $unique, $reference, $offset, $lineno, $this->getTag());
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

    /**
     * @param Twig_TokenStream $stream
     * @param string $name
     * @return Twig_Node
     */
    protected function bodyParse(Twig_TokenStream $stream, $name)
    {
        if ($stream->nextIf(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            if ($token = $stream->nextIf(Twig_Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new Twig_Error_Syntax(
                        sprintf("Expected enddefer for defer '$name' (but %s given)", $value),
                        $stream->getCurrent()->getLine(),
                        $stream->getFilename()
                    );
                }
            }
        } else {
            throw new Twig_Error_Syntax(
                "Expected enddefer for defer '$name'",
                $stream->getCurrent()->getLine(),
                $stream->getFilename()
            );
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return $body;
    }

    /**
     * @param Twig_Token $token
     * @param $reference
     * @return string
     */
    private function createUniqueBlockName(Twig_Token $token, $reference)
    {
        $name = $this->blockPrefix . $reference . sha1($this->parser->getFilename() . $token->getLine());
        if (!$this->parser->hasBlock($name)) {
            return $name;
        }

        $i = 0;
        do {
            $tmpName = $name . '_' . ($i++);
        } while ($this->parser->hasBlock($tmpName));

        return $tmpName;
    }
}
