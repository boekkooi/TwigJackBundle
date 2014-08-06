<?php
namespace Boekkooi\Bundle\TwigJackBundle\Twig\Loader;

use Boekkooi\Bundle\TwigJackBundle\Model\TemplateInterface;
use Boekkooi\Bundle\TwigJackBundle\Model\TranslatableTemplateInterface;
use Doctrine\Common\Persistence\ObjectRepository;

class DoctrineLoader implements \Twig_LoaderInterface
{
    const KEY_SEPARATOR = '|';

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var callable|null
     */
    protected $localeCallable;

    /**
     * Constructor
     *
     * @param ObjectRepository $repository     The template repository
     * @param string           $prefix         The template prefix to identify database templates
     * @param callable|null    $localeCallable
     */
    public function __construct(ObjectRepository $repository, $prefix = 'database::', $localeCallable = null)
    {
        if ($localeCallable !== null && !is_callable($localeCallable)) {
            throw new \InvalidArgumentException('Given `localeCallable` must be a callable or NULL.');
        }

        $this->repository = $repository;
        $this->prefix = $prefix;
        $this->localeCallable = $localeCallable;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return $this->findTemplate($name)->getTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        $template = $this->findTemplate($name);

        if ($template instanceof TranslatableTemplateInterface) {
            /** @var TranslatableTemplateInterface $template */
            return $this->prefix . self::KEY_SEPARATOR . $template->getCurrentLocale() . self::KEY_SEPARATOR . $template->getIdentifier();
        }
        return $this->prefix . self::KEY_SEPARATOR . $template->getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->findTemplate($name)->getLastModified()->getTimestamp() <= $time;
    }

    /**
     * Check if the given name has the correct prefix for loading.
     *
     * @param  string $name The name of the template to check
     * @return bool
     */
    protected function isLoadableTemplate($name)
    {
        return !empty($name) && (empty($this->prefix) || strlen($name) > strlen($this->prefix) && strpos($name, $this->prefix) === 0);
    }

    /**
     * Find a template by it's name
     *
     * @param  string             $name The name of the template to find
     * @return TemplateInterface
     * @throws \Twig_Error_Loader
     */
    protected function findTemplate($name)
    {
        if (!$this->isLoadableTemplate($name)) {
            throw new \Twig_Error_Loader(sprintf('Malformed namespaced template name "%s" (expecting "%stemplate_name").', $name, $this->prefix));
        }

        $templateIdentifier = substr($name, strlen($this->prefix));
        $template = $this->repository->find($templateIdentifier);
        if ($template === null) {
            throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $name));
        }
        if (!($template instanceof TemplateInterface)) {
            throw new \Twig_Error_Loader(sprintf('Unexpected template type "%s" found for template "%s".', get_class($template), $name));
        }
        if ($template instanceof TranslatableTemplateInterface) {
            /** @var TranslatableTemplateInterface $template */
            $locale = $this->getCurrentLocale();
            $template->setCurrentLocale($locale);
        }

        return $template;
    }

    protected function getCurrentLocale()
    {
        if ($currentLocaleCallable = $this->localeCallable) {
            $locale = call_user_func($currentLocaleCallable);
            if ($locale) {
                return $locale;
            }
        }

        return 'en';
    }
}
