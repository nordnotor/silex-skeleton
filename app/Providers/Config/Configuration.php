<?php

namespace App\Providers\Config;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Class Configuration
 * @package App\Providers\Config
 *
 * @property array $directories
 * @property LoaderInterface[] $loaders
 *
 * @property string $cacheFile
 *
 * @property ConfigCache $cache
 * @property Processor $processor
 * @property FileLocator $locator
 * @property LoaderResolver $loaderResolver
 * @property DelegatingLoader $delegatingLoader
 *
 * @property array $configs
 */
class Configuration
{
    public $debug;

    public $loaders;
    public $directories;

    public $cacheFile;

    public $cache;
    public $locator;
    public $processor;
    public $loaderResolver;
    public $delegatingLoader;

    protected $configs;

    public function __construct(string $cachePath, array $directories, array $loaders, $debug = false)
    {
        $this->debug = $debug;

        $this->directories = $directories;
        $this->locator = new FileLocator($this->directories);

        foreach ($loaders as $key => $loader) {
            $this->loaders[$key] = new $loader($this->locator);
        }

        $this->cacheFile = $cachePath . '/configuration/configuration.php.cache';

        $this->processor = new Processor();
        $this->cache = new ConfigCache($this->cacheFile, $debug);
        $this->loaderResolver = new LoaderResolver($this->loaders);
        $this->delegatingLoader = new DelegatingLoader($this->loaderResolver);
    }

    public function load($resource, $type = null)
    {
        if (!$this->cache->isFresh() || $this->debug) {

            $results = $this->delegatingLoader->load($this->locator->locate($resource), $type);

            $this->cache->write(sprintf('<?php return %s;', var_export($results, true)), [
                new FileResource($this->locator->locate($resource))
            ]);
        }

        $this->configs = require $this->cache->getPath();

        return $this;
    }

    public function progress(ConfigurationInterface $configuration)
    {
        return $this->processor->processConfiguration($configuration, $this->configs);
    }
}