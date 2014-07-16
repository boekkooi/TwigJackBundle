Template exclamation syntax
=============
This templating tweak introduces the `!` syntax. 
Inspired by on [Symfony issue 1966](https://github.com/symfony/symfony/issues/1966) and [Twig issue 1334](https://github.com/fabpot/Twig/issues/1334).

*(Please do keep in mind that this will totally ignore the bundle [inheritance](http://symfony.com/doc/current/cookbook/bundles/inheritance.html) and it will just use the original bundle.)*

Example
-------------
```jinja
{# app/Resources/MyBundle/views/layout.html.twig #}
{% extends "!MyBundle::layout.html.twig" %}

{# override you block here #}
```
The code above extends the for example `src/MyBundle/Resources/views/layout.html.twig` and not it's self (default twig + symfony behaviour).
