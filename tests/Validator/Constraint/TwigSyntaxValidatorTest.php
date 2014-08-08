<?php
namespace Tests\Boekkooi\Bundle\TwigJackBundle\Validator\Constraint;

use Boekkooi\Bundle\TwigJackBundle\Validator\Constraint\TwigSyntax;
use Boekkooi\Bundle\TwigJackBundle\Validator\Constraint\TwigSyntaxValidator;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 * @covers \Boekkooi\Bundle\TwigJackBundle\Validator\Constraint\TwigSyntaxValidator
 */
class TwigSyntaxValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $template = '{{ variable }}';
        $templateStream = $this->getMockTokenStream();

        $env = $this->getMockEnvironment();
        $env->expects($this->once())->method('tokenize')->with($template)->willReturn($templateStream);
        $env->expects($this->once())->method('parse')->with($templateStream);

        $validator = new TwigSyntaxValidator($env);
        $validator->initialize($this->getMockContext());
        $validator->validate($template, new TwigSyntax());
    }

    public function testValidateErrorToken()
    {
        $message = 'My message';
        $template = '{{ variable }}';

        $env = $this->getMockEnvironment();
        $env->expects($this->never())->method('parse')->withAnyParameters();
        $env->expects($this->once())->method('tokenize')->with($template)
            ->willThrowException(new \Twig_Error_Syntax('error'));

        $validator = new TwigSyntaxValidator($env);
        $validator->initialize($this->getMockContext($message, $template));

        $validator->validate($template, new TwigSyntax(array('message' => $message)));
    }

    public function testValidateErrorParse()
    {
        $message = 'My message';
        $template = '{{ variable }}';
        $templateStream = $this->getMockTokenStream();

        $env = $this->getMockEnvironment();
        $env->expects($this->once())->method('tokenize')->with($template)->willReturn($templateStream);
        $env->expects($this->once())->method('parse')->with($templateStream)
            ->willThrowException(new \Twig_Error_Syntax('error'));

        $validator = new TwigSyntaxValidator($env);
        $validator->initialize($this->getMockContext($message, $template));

        $validator->validate($template, new TwigSyntax(array('message' => $message)));
    }

    public function testValidateNoParse()
    {
        $message = 'My message';
        $template = '{{ variable }}';
        $templateStream = $this->getMockTokenStream();

        $env = $this->getMockEnvironment();
        $env->expects($this->once())->method('tokenize')->with($template)->willReturn($templateStream);
        $env->expects($this->never())->method('parse')->withAnyParameters();

        $validator = new TwigSyntaxValidator($env);
        $validator->initialize($this->getMockContext());

        $validator->validate($template, new TwigSyntax(array('parse' => false)));
    }

    public function testValidateCustomEnvironment()
    {
        $validator = new TwigSyntaxValidator($this->getMockEnvironment(true));
        $validator->initialize($this->getMockContext());

        $template = '{{ variable }}';
        $templateStream = $this->getMockTokenStream();

        $env = $this->getMockEnvironment();
        $env->expects($this->once())->method('tokenize')->with($template)->willReturn($templateStream);
        $env->expects($this->once())->method('parse')->with($templateStream);

        $validator->validate($template, new TwigSyntax(array('environment' => $env)));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateInvalidConstraint()
    {
        $template = '{{ variable }}';

        $validator = new TwigSyntaxValidator($this->getMockEnvironment(true));
        $validator->initialize($this->getMockContext());

        $validator->validate($template, $this->getMock('\\Symfony\\Component\\Validator\\Constraint'));
    }

    /**
     * @dataProvider getEmptyValues
     */
    public function testValidateEmptyValue($template)
    {
        $validator = new TwigSyntaxValidator($this->getMockEnvironment(true));
        $validator->initialize($this->getMockContext());

        $validator->validate($template, new TwigSyntax());
    }

    public function getEmptyValues()
    {
        return array(
            array(null),
            array(''),
        );
    }

    protected function getMockEnvironment($neverInvoked = false)
    {
        $env = $this->getMockBuilder('\\Twig_Environment')->disableOriginalConstructor()->getMock();
        if ($neverInvoked === false) {
            return $env;
        }

        $env->expects($this->never())->method('tokenize');
        $env->expects($this->never())->method('parse');
        return $env;
    }

    protected function getMockContext($message = false, $template = '')
    {
        $context = $this->getMock('\\Symfony\\Component\\Validator\\ExecutionContextInterface');

        if ($message !== false) {
            $context = $this->getMock('\\Symfony\\Component\\Validator\\ExecutionContextInterface');
            $context->expects($this->once())->method('addViolation')->with($message, array('{{ value }}' => '"'.$template.'"'));
        } else {
            $context->expects($this->never())->method('addViolation')->withAnyParameters();
        }

        return $context;
    }

    protected function getMockTokenStream()
    {
        return $this->getMockBuilder('\\Twig_TokenStream')->disableOriginalConstructor()->getMock();
    }
} 