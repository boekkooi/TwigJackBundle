Twig Jack Repository
=============
[![Build Status](https://travis-ci.org/boekkooi/TwigJackBundle.svg?branch=master)](https://travis-ci.org/boekkooi/TwigJackBundle)[![Code Coverage](https://scrutinizer-ci.com/g/boekkooi/TwigJackBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/boekkooi/TwigJackBundle/?branch=master)[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/boekkooi/TwigJackBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/boekkooi/TwigJackBundle/?branch=master)[![Total Downloads](https://poser.pugx.org/boekkooi/twig-jack-bundle/downloads.svg)](https://packagist.org/packages/boekkooi/twig-jack-bundle)[![Latest Stable Version](https://poser.pugx.org/boekkooi/twig-jack-bundle/v/stable.svg)](https://packagist.org/packages/boekkooi/twig-jack-bundle)[![License](https://poser.pugx.org/boekkooi/twig-jack-bundle/license.svg)](https://packagist.org/packages/boekkooi/twig-jack-bundle)[![SensioLabsInsight](https://insight.sensiolabs.com/projects/53a6e635-78ef-4c6c-be8d-760e978839ff/mini.png)](https://insight.sensiolabs.com/projects/53a6e635-78ef-4c6c-be8d-760e978839ff)

This repository hosts Twig Extensions and Template tweaks for the symfony 2 framework.

BoekkooiTwigJackBundle is using [Semantic Versioning](http://semver.org/) starting with version 1.1.0.

Fork this repository, add your extension, and request a pull.

[Install and configure](doc/configuration.md)
-------------
`composer require boekkooi/twig-jack-bundle dev-master`
    
[The Defer Block](doc/twig-defer.md)
-------------
A defer/append twig block. [more...](doc/twig-defer.md)

[The Exclamation Syntax](doc/templating-exclamation.md)
-------------
Use `{% extends "!@<bundle>" %}` to inherit from the root bundle. [more...](doc/templating-exclamation.md)

[The Doctrine Loader](doc/twig-doctrine-loader.md)
-------------
Add one or multiple doctrine/database template loaders to twig with optional translation support. [more...](doc/twig-doctrine-loader.md)

[Twig syntax constraint](doc/twig-syntax-validator.md)
-------------
Validate that a string is a valid twig template. [more...](doc/twig-syntax-validator.md)
