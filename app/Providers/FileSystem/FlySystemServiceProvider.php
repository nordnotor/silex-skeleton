<?php

namespace App\Providers\FileSystem;

use Pimple\Container;
use Silex\Application;
use League\Flysystem\Filesystem;
use Pimple\ServiceProviderInterface;
use League\Flysystem\Plugin\ListWith;
use League\Flysystem\Plugin\EmptyDir;
use League\Flysystem\Plugin\ListFiles;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\AdapterInterface;
use Silex\Api\BootableProviderInterface;
use League\Flysystem\Plugin\AbstractPlugin;
use League\Flysystem\Plugin\GetWithMetadata;

/**
 * Class FlySystemServiceProvider
 * @package App\Providers\Eloquent
 * @property \App\Application $app
 *
 * https://flysystem.thephpleague.com/
 *
 * @REQUIRE: composer require league/flysystem
 *
 * @REGISTER:
 *   $app->register(new \e1\providers\File\FlysystemServiceProvider(), [
 *       'flysystem.filesystems' => [
 *           'tmp' => [
 *               'adapter' => 'League\Flysystem\Adapter\Local',
 *               'args' => [__DIR__ . '/tmp'],
 *               'config' => [],
 *           ],
 *           'pdf' => [
 *               'adapter' => 'League\Flysystem\Adapter\Local',
 *               'args' => [__DIR__ . '/pdf'],
 *               'config' => [],
 *           ],
 *       ],
 *   ]);
 *
 * @USAGE:
 *   $app['flysystem']('pdf') # get need Flysystem by name
 *   $app['flysystems']  # get all Flysystems[]
 *
 */
class FlySystemServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public $app;

    public $plugins = [
        EmptyDir::class,
        ListWith::class,
        ListFiles::class,
        ListPaths::class,
        GetWithMetadata::class,
    ];

    public function boot(Application $app)
    {
        $app['flysystem.init']();
    }

    public function register(Container $app)
    {
        $app['flysystems'] = function () use ($app) {

            $flySystems = new Container();
            foreach ($app['flysystem.filesystems'] as $alias => $parameters) {
                /** @var string $alias */
                $flySystems[$alias] = $this->buildFilesystem($parameters);
            }
            return $flySystems;
        };

        $app['flysystem'] = $app->protect(function (string $name) use ($app) {
            return $this->buildFilesystem($app['flysystem.filesystems'][$name]);
        });

        $app['flysystem.init'] = $app->protect(function () use ($app) {

            $app['flysystem.plugins'] = $app['flysystem.plugins'] ?? [];
            $app['flysystem.filesystems'] = $app['flysystem.filesystems'] ?? [];

            # add plugin to default FlySystem
            foreach ($app['flysystem.plugins'] as $plugin) {
                if (!in_array($plugin, $this->plugins, false) && $plugin instanceof AbstractPlugin) {
                    $this->plugins[] = $plugin;
                }
            }

            # create plugins
            foreach ($this->plugins as $key => $plugin) {
                $this->plugins[$key] = new $plugin;
            }

            # Create FlySystem
            foreach ($app['flysystem.filesystems'] as $alias => $parameters) {
                /** @var string $alias */
                $app[$alias] = $this->buildFilesystem($parameters);
            }

        });
    }

    protected function buildFilesystem(array $parameters)
    {
        /** @var AdapterInterface $adapter */
        $adapter = (new \ReflectionClass($parameters['adapter']))->newInstanceArgs($parameters['args']);

        $parameters = $parameters['config'] ?? null;

        $filesystem = new Filesystem($adapter, $parameters);

        foreach ($this->plugins as $plugin) {
            $plugin->setFilesystem($filesystem);
            $filesystem->addPlugin($plugin);
        }
        return $filesystem;
    }
}

