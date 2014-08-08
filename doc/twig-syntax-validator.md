Twig Syntax Validator
=============
This Twig Syntax Validator adds an validation constraint that will validate a string as containing valid twig syntax.

Example
-------------
Enable the validator
```YAML
# app/config/config.yml
boekkooi_twig_jack:
    ...
    constraint: true
```

Use the constraint.
```php
use Boekkooi\Bundle\TwigJackBundle\Validator\Constraint as TwigAssert;
use Symfony\Component\Validator\Constraints as Assert;

class MyClass {
    // ...

    /**
     * @Assert\NotBlank()
     * @TwigAssert\TwigSyntax()
     */
    public $template;

    /**
     * Check this variable based on syntax only and not availability of the methods, filters, etc. used
     *
     * @Assert\NotBlank()
     * @TwigAssert\TwigSyntax(parse=false)
     */
    public $remote_template;

    // ...
}
```

Full configuration
-------------
Default configuration:
```yaml
# app/config/config.yml
boekkooi_twig_jack:
    constraint:
        enabled: true
        environment: 'twig' # Reference to the twig environment service to use by default.
```
