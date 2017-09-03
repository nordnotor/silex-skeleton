<?php

namespace App;

use Silex\Application\TwigTrait;
use Silex\Application\MonologTrait;
use App\Providers\Rbac\SecurityTrait;
use Silex\Application\UrlGeneratorTrait;
use App\Interfaces\ServiceProviderInterface;
use App\Providers\Config\ConfigurationServiceProvider;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use App\Providers\Config\Interfaces\ApplicationConfigurationInterface;

class Application extends \Silex\Application implements ApplicationConfigurationInterface
{
    use UrlGeneratorTrait, MonologTrait, TwigTrait;

    /**
     * Application constructor.
     * @param array $values
     */
    public function __construct($values = [])
    {
        parent::__construct($values);

        $this['route_class'] = Route::class;
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this['configuration.load'];

        parent::boot();
    }

    /**
     * @param string $name
     * @return \Jenssegers\Mongodb\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function model($name)
    {
        return $this['model']($name);
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    public function request()
    {
        return $this['request_stack']->getCurrentRequest();
    }

    /**
     * @param array $data
     * @param string|null $template
     * @param int $status
     * @return array
     */
    public function renderResult(array $data = [], string $template = null, $status = 200): array
    {
        return [$template, $status, $data];
    }


    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode->ignoreExtraKeys(false);

        $rootNode->children()
            ->scalarNode('name')->defaultValue('Silex App')->end()
            ->arrayNode('providers')->info('Providers configuration.')->children();

        foreach ($this->providers as $provider) {
            if ($provider instanceof ServiceProviderInterface) {
                $rootNode->append($provider->getConfigTreeBuilder());
            }
        }

        return $treeBuilder;
    }

    public function configurationDirs(): array
    {
        return [
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config',
        ];
    }

    public function loadConfiguration(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                foreach ($value as $name => $item) {
                    $this["$key.$name"] = $item;
                }
                continue;
            }
            $this[$key] = $value;
        }
    }
}

