Twig Doctrine Loader
=============
This extension allows you to add one or multiple Twig Doctrine Loaders to you system.
By default, this extension is disabled.

Add a loader
-------------
```YAML
# app/config/config.yml
boekkooi_twig_jack:
    ...
    loaders:
        my_loader:
            prefix: 'db://'
            type: orm
            model_class: 'My\Model\Template'
```
The above configuration will add a custom loader for any template that starts with `db://`.

To make you loader be i18n compatible you can add the `locale_callable` that points to a service in the container.
This service must return a callable that returns the locale string.

Example
-------------
This example is using [knplabs/doctrine-behaviors](https://packagist.org/packages/knplabs/doctrine-behaviors) for the translations.

First we create the required models:
```PHP
<?php
# My\Model\Template
namespace My\Model;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 * @ORM\Table(name="my_template")
 *
 * @method TemplateTranslation translate(string $locale=null)
 */
class Template implements TemplateInterface
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=100)
     */
    protected $identifier;

    /**
     * Constructor
     *
     * @param string $identifier The template unique identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->translate()->getTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified()
    {
        return $this->translate()->getLastModified();
    }
}
```

```PHP
<?php
# My\Model\Template
namespace My\Model;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 * @ORM\Table(name="my_template_translation")
 */
class TemplateTranslation
{
    use ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="text")
     */
    protected $template = '';

    public function __construct()
    {
        $this->updateTimestamps();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        Assertion::string($template);

        $this->template = $template;
    }

    public function getLastModified()
    {
        return $this->updatedAt ?: $this->createdAt;
    }
}
```

Add the current locale callable to your services:
```
services:
  my.current_locale_callable:
    class: Knp\DoctrineBehaviors\ORM\Translatable\CurrentLocaleCallable
    arguments:
        - @service_container
```

Now we add the need configuration.
```YAML
# app/config/config.yml
boekkooi_twig_jack:
    ...
    loaders:
        my_loader:
            prefix: 'db://'
            type: orm
            model_class: 'My\Model\Template'
            locale_callable: 'my.current_locale_callable'
```

Your done! Let's use it:

```PHP
# Some controller or mailer or what ever
$this->container->get('templating')->render('db://my_template', array());
```

Full configuration
-------------
```YAML
# app/config/config.yml
boekkooi_twig_jack:
    ...
    loaders:
        my_loader:
            prefix: 'db://' # A template name prefix
            type: orm # Choose orm, mongo, couch or custom
            repository: 'my_custom_repo' # Only used when type is custom
            model_class: 'My\Model\Template'
            locale_callable: 'current_locale_callable' # A service identifier that when invoked returns the locale to use
```
