<?php
namespace Boekkooi\Bundle\TwigJackBundle\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer as BaseTemplatePathsCacheWarmer;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class TemplatePathsCacheWarmer extends BaseTemplatePathsCacheWarmer
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct(TemplateFinderInterface $finder, TemplateLocator $locator, KernelInterface $kernel)
    {
        parent::__construct($finder, $locator);
        $this->kernel = $kernel;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $templates = array();

        foreach ($this->finder->findAllTemplates() as $template) {
            $templates[$template->getLogicalName()] = $this->locator->locate($template);

            if (!$template instanceof TemplateReference) {
                continue;
            }

            $bundle = $template->get('bundle');
            if (empty($bundle)) {
                continue;
            }
            // Resolve the base bundle
            $bundles = $this->kernel->getBundle($bundle, false);
            $baseBundle = end($bundles);

            // Resolve the base path
            $relativePath = substr($template->getPath(), strlen($bundle) + 1);
            $baseBundlePath = $baseBundle->getPath() . $relativePath;

            // Check if the base is the same if not add the override to the template cache
            if ($templates[$template->getLogicalName()] !== $baseBundlePath) {
                $templates['!'.$template->getLogicalName()] = $baseBundlePath;
            }
        }

        $this->writeCacheFile($cacheDir.'/templates.php', sprintf('<?php return %s;', var_export($templates, true)));
    }
}
