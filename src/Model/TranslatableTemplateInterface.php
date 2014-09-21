<?php
namespace Boekkooi\Bundle\TwigJackBundle\Model;


/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface TranslatableTemplateInterface extends TemplateInterface
{
    /**
     * Get the templates current locale
     *
     * @return string The current locale
     */
    public function getCurrentLocale();

    /**
     * Set the templates new locale
     *
     * @param string $locale The current locale
     */
    public function setCurrentLocale($locale);
}
