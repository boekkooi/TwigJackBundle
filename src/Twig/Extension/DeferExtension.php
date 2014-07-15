<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Extension;

use Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferExtension extends \Twig_Extension
{
    protected $references = array();
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new TokenParser\Defer()
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('defer', null, array(
                'node_class' => 'Boekkooi\Bundle\TwigJackBundle\Twig\Node\Expression\DeferReference',
            ))
        );
    }

    public function cache($type, $name, $content)
    {
        if (!isset($this->references[$type])) {
            $this->references[$type] = array();
        }
        $this->references[$type][$name] = $content;
    }

    public function retrieve($type, $clear = true)
    {
        if (!isset($this->references[$type])) {
            return array();
        }

        $rtn = $this->references[$type];
        if ($clear) {
            unset($this->references[$type]);
        }
        return $rtn;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'defer';
    }
}
