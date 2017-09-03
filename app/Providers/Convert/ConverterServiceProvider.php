<?php

namespace App\Providers\Convert;

use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use App\Interfaces\ServiceProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class ConverterServiceProvider
 * @package App\Providers\Convert
 *
 * @property \App\Application $app
 * @property array $converters
 *
 * @REQUIRE:
 *    -
 *
 * @REGISTER:
 * $app->register(new \App\Providers\Convert\ConverterServiceProvider(),[
 *          'converter.callbacks' => [
 *              \App\Providers\Convert\Converter\Model::class,
 *          ],
 * ]);
 *
 * @USAGE:
 *
 * $converterCallable = $app['сonverter']->get($converterName, $params); # return callable
 *
 * $controllers->get('/{id}', [$this, 'view'])->convert('model', $converterCallable);
 *
 */
class ConverterServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    protected $app;
    protected $converters = [];

    public function boot(Application $app, array $config = [])
    {
        $app['converter.init'];
    }

    public function register(Container $app)
    {
        $this->app = $app;
        $app['converter'] = $this;

        $app['converter.init'] = function () use ($app) {

            $converters = $app['converter.callbacks'] ?? [];

            foreach ($converters as $class) {

                if (!class_exists($class)) {
                    throw new \Exception("$class does not exist", 500);
                }

                if (!(new \ReflectionClass($class))->isSubclassOf(ConverterCore::class)) {
                    throw new \Exception("instanceof class an ConverterCore instance, $class given", 500);
                }

                $this->add($class::converterName(), function (array $params) use ($class) {
                    return new $class($this->app, $params);
                });
            }
        };
    }

    public function add(string $name, callable $callback)
    {
        $this->converters[$name] = $callback;
    }

    public function get(string $name, array $params = [], string $method = 'convert'): callable
    {
        return function ($attribute = null, Request $request) use ($name, $params, $method) {
            return call_user_func([$this->converters[$name]($params), $method], $attribute, $request);
        };
    }

    /**
     * Generates the configuration tree builder.
     * @return NodeDefinition
     */
    public function getConfigTreeBuilder(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('converter');

        $node->children()
            ->arrayNode('callbacks')->requiresAtLeastOneElement()->prototype('scalar')->end()->end()->end()
            ->end();

        return $node;
    }
}