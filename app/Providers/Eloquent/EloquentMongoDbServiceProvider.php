<?php

namespace App\Providers\Eloquent;

use App\Model;
use Pimple\Container;
use Silex\Application;
use Silex\Api\BootableProviderInterface;
use App\Interfaces\ServiceProviderInterface;

use Illuminate\Events\Dispatcher;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * Class EloquentMongoDbServiceProvider
 * @package App\Providers\Eloquent
 *
 * @REQUIRE:
 *
 * @see: https://github.com/jenssegers/laravel-mongodb
 *  composer require jenssegers/mongodb
 *
 * @REGISTER:
 * $app->register(new \App\Providers\Eloquent\EloquentMongoDbServiceProvider(), [
 *      'eloquent.model_namespace' => '\\App\\Models\\',
 *      'eloquent.default_connection' => 'mongodb',
 *      'eloquent.connections' => [
 *          'mongodb' => [
 *              'driver' => 'mongodb',
 *              'host' => '127.0.0.1',
 *              'port' => '27017',
 *              'database' => 'ln',
 *              'charset' => 'utf8',
 *              'collation' => 'utf8_unicode_ci',
 *          ]
 *      ]
 * ]);
 *
 * @USAGE:
 *
 *  # query example
 *  $app->model('collection_name')
 *      ->where('begin', '>=', $begin)
 *      ->where('begin', '<', $end)
 *      ->where('state', '!=', MODEL::STATE_CANCELED)
 *      ->get();
 */
class EloquentMongoDbServiceProvider implements BootableProviderInterface, ServiceProviderInterface
{
    protected $app;
    protected $capsule;
    protected $default;
    protected $namespace;

    public function boot(Application $app, array $config = [])
    {
        $app['eloquent.init']();
    }

    public function register(Container $app)
    {
        $this->app = $app;
        $app['eloquent.init'] = $app->protect(function () use ($app) {

            $connections = $app['eloquent.connections'];
            $this->namespace = $app['eloquent.model_namespace'];
            $this->default = $app['eloquent.default_connection'];

            $this->capsule = new Capsule;

            $this->capsule->getDatabaseManager()->extend('mongodb', function ($config) {
                return new \Jenssegers\Mongodb\Connection($config);
            });

            foreach ($connections as $name => $driverConfig) {
                $this->capsule->addConnection($driverConfig, $this->default === $name ? 'default' : $name);
            }

            $this->capsule->setEventDispatcher($app['eloquent.dispatcher']);
            $this->capsule->setAsGlobal();
            $this->capsule->bootEloquent();

            Eloquent::addGlobalScope('app', function () use ($app) {
                return $app;
            });

            $app['eloquent.capsule'] = $this->capsule;
        });

        $app['eloquent.dispatcher'] = function () use ($app) {
            return new Dispatcher();
        };

        $app['model'] = $app->protect(function ($name) use ($app) {

            $class_name = $this->namespace . ucfirst($name);

            $model = class_exists($class_name) ? new $class_name() : new Model();

            return $model;
        });
    }

    public function getConfigTreeBuilder(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('eloquent');

        $node->disallowNewKeysInSubsequentConfigs()->children()
            ->scalarNode('default_connection')->defaultValue('default')->end()
            ->scalarNode('model_namespace')->defaultValue('\\App\\Models\\')->end()
            ->arrayNode('connections')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->scalarNode('name')->end()
            ->scalarNode('driver')->end()
            ->scalarNode('host')->end()
            ->scalarNode('port')->end()
            ->scalarNode('database')->end()
            ->scalarNode('username')->end()
            ->scalarNode('password')->end()
            ->scalarNode('charset')->end()
            ->scalarNode('collation')->end()
            ->end()
            ->end()
            ->end();

        return $node;
    }
}