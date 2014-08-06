<?php
namespace Boekkooi\Bundle\TwigJackBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TwigSyntaxValidator extends ConstraintValidator
{
    protected $environment;

    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TwigSyntax) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TwigSyntax');
        }

        if ($value === '' || $value === null) {
            return;
        }

        $env = $this->getEnvironment($constraint);
        try {
            $tokeStream = $env->tokenize($value);
            if ($constraint->parse) {
                $env->parse($tokeStream);
            }
        } catch (\Twig_Error_Syntax $e) {
            $this->context->addViolation($constraint->message, array(
                '{{ value }}' => $this->formatValue($value),
            ));
        }
    }

    protected function getEnvironment(TwigSyntax $constraint)
    {
        $env = $constraint->environment;
        if ($env !== null) {
            return $env;
        }
        return $this->environment;
    }
} 