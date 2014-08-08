<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Validator\Constraint;
use Boekkooi\Bundle\TwigJackBundle\Validator\Constraint\TwigSyntax;


/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TwigSyntaxTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorDefault()
    {
        $constraint = new TwigSyntax();

        $this->assertEquals('This value is not a valid twig template.', $constraint->message);
        $this->assertNull($constraint->environment);
        $this->assertTrue($constraint->parse);
    }

    public function testConstructorArguments()
    {
        $env = $this->getMockBuilder('\\Twig_Environment')->disableOriginalConstructor()->getMock();
        $constraint = new TwigSyntax(array(
            'parse' => false,
            'environment' => $env,
            'message' => 'message'
        ));
        $this->assertEquals('message', $constraint->message);
        $this->assertEquals($env, $constraint->environment);
        $this->assertFalse($constraint->parse);

        $constraint = new TwigSyntax(array(
            'environment' => null,
            'message' => 'message'
        ));
        $this->assertEquals('message', $constraint->message);
        $this->assertNull($constraint->environment);
        $this->assertTrue($constraint->parse);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\InvalidOptionsException
     */
    public function testConstructorInvalid()
    {
        new TwigSyntax(array('environment' => 'invalid'));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testConstructorDefaultValue()
    {
        new TwigSyntax('default');
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\InvalidOptionsException
     */
    public function testConstructorNoOption()
    {
        new TwigSyntax(array('no_option' => true));
    }

    public function testValidatedBy()
    {
        $constraint = new TwigSyntax();
        $this->assertEquals('TwigSyntaxValidator', $constraint->validatedBy());
    }
} 