<?php

namespace App\Providers\Config;

use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use App\Providers\Config\Interfaces\ApplicationConfigurationInterface;

use App\Providers\Config\Loaders\{
    PhpLoader, TomlLoader, YamlLoader, JsonLoader
};

/**
 * Class ConfigServiceProvider
 * @package App\Providers\Config
 *
 * @property Configuration $client
 */
class ConfigurationServiceProvider implements ServiceProviderInterface
{
    public $client;

    public $ext;
    public $cacheDir;
    public $environment;

    public function __construct(string $ext, string $environment, string $cacheDir)
    {
        $this->ext = $ext;
        $this->cacheDir = $cacheDir;
        $this->environment = $environment;
    }

    public function register(Container $app)
    {
        /** @var Application|\App\Application $app */
        if (!$app instanceof ApplicationConfigurationInterface) {
            $app->abort("\$app must be instance of ApplicationConfigurationInterface.");
        }

        $this->client = new Configuration($this->cacheDir, $app->configurationDirs(), !empty($loaders) ? $loaders : [
            TomlLoader::class,
            YamlLoader::class,
            JsonLoader::class,
            PhpLoader::class,
        ], $app['debug']);

        $app['configuration'] = function () use ($app) {
            return $this->client;
        };

        $app['configuration.load'] = function () use ($app) {

            $configuration = $app['configuration']->load("{$this->environment}.{$this->ext}")->progress($app);

            array_walk_recursive($configuration, function (&$item, $key) use ($app) {


                if (preg_match("/^[@][a-zA-Z0-9_.]*/", $item, $match)) {

                    $name = trim($match[0], "@");

                    if (isset($app[$name])) {

                        if (is_object($app[$name])) {
                            $item = $app[$name];
                        } else {
                            $item = str_replace($match[0], $app[$name], $item);
                        }
                    }
                }
            });

            $app->loadConfiguration($configuration);
        };
    }
}