<?php
namespace Boekkooi\Bundle\TwigJackBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TwigSyntax extends Constraint
{
    public $message = 'This value is not a valid twig template.';
    public $parse = true;
    public $environment = null;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if ($this->environment !== null && !$this->environment instanceof \Twig_Environment) {
            throw new InvalidOptionsException(sprintf('Option "environment" must be null or a Twig_Environment instance for constraint %s', __CLASS__), array('environment'));
        }
    }

    public function validatedBy()
    {
        return 'TwigSyntaxValidator';
    }
}