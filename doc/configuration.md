Installation
============

Add BoekkooiTwigJackBundle by running the command:
```
composer require boekkooi/twig-jack-bundle dev-master
```

Enable the bundle in the kernel:
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Boekkooi\Bundle\TwigJackBundle\BoekkooiTwigJackBundle(),
    );
}
```

Configure the BoekkooiTwigJackBundle:
```yaml
# app/config/config.yml
boekkooi_twig_jack:
    defer: true
    exclamation: true
```
