<?php
namespace Boekkooi\Bundle\TwigJackBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseTemplateNameParser;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference as FrameworkTemplateReference;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TemplateNameParser extends BaseTemplateNameParser
{
    public function parse($name)
    {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        }

        // Not a override
        if ($name[0] !== '!') {
            return parent::parse($name);
        }

        // normalize name (see TemplateNameParser line 52)
        $name = substr($name, 1);
        $name = str_replace(':/', ':', preg_replace('#/{2,}#', '/', strtr($name, '\\', '/')));
        if (isset($this->cache['!' . $name])) {
            return $this->cache['!' . $name];
        }

        // Get the parent reference
        $reference = parent::parse($name);
        if (!$reference instanceof FrameworkTemplateReference) {
            return $reference;
        }

        $bundle = $reference->get('bundle');
        if (empty($bundle)) {
            return $reference;
        }

        // Resolve the base template path
        $path = $reference->getPath();
        $relativePath = substr($path, strlen($bundle) + 1);

        $bundles = $this->kernel->getBundle($bundle, false);
        $relativePath = end($bundles)->getPath() . $relativePath;

        return $this->cache['!' . $name] = new TemplateReference($relativePath, $reference);
    }
}
