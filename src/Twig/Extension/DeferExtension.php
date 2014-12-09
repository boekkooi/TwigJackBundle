<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Extension;

use Boekkooi\Bundle\TwigJackBundle\Twig\TokenParser;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeferExtension extends \Twig_Extension
{
    protected $references = array();

    protected $subReferences = array();

    protected $deferBlockPrefix;

    public function __construct($deferBlockPrefix = '_defer_ref_')
    {
        $this->deferBlockPrefix = $deferBlockPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new TokenParser\Defer($this->deferBlockPrefix)
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

    public function cache($type, $content, $name = null, $offset = null)
    {
        if (!isset($this->references[$type])) {
            $this->references[$type] = array();
            $this->subReferences[$type] = array();
        }

        if ($offset === null || $offset >= count($this->references[$type])) {
            $this->references[$type][] = $content;
        } elseif ($offset <= 0) {
            array_unshift($this->references[$type], $content);
        } else {
            array_splice($this->references[$type], $offset, 0, $content);
        }

        if ($name !== null) {
            $this->subReferences[$type][] = $name;
        }
    }

    public function contains($type, $name)
    {
        return isset($this->subReferences[$type]) && in_array($name, $this->subReferences[$type], true);
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
