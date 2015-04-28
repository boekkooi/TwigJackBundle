<?php
namespace Boekkooi\Bundle\TwigJackBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference as BaseTemplateReference;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TemplateReference extends BaseTemplateReference
{
    public function __construct($path, BaseTemplateReference $childReference)
    {
        parent::__construct(
            $childReference->get('bundle'),
            $childReference->get('controller'),
            $childReference->get('name'),
            $childReference->get('format'),
            $childReference->get('engine')
        );

        $this->parameters['path'] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->get('path');
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return '!' . parent::getLogicalName();
    }
}
