<?php

namespace App\Providers\illuminateTranslation;

use Pimple\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use App\Interfaces\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class TranslationServiceProvider
 * @package App\Providers\illuminateTranslation
 *
 * @REQUIRE:
 *
 * @see: https://github.com/illuminate/translation
 * composer require illuminate/translation
 *
 * @REGISTER:
 *
 * $app->register(new \App\Providers\illuminateTranslation\TranslationServiceProvider(), [
 *     'translator.path' => dirname(__DIR__) . '/resources/lang',
 *     'translator.locales' => 'ru|en|ua',
 *     'translator.fallback_locale' => 'en',
 * ]);
 *
 * @USAGE:
 *
 * $app['translator']->get("message.path", $data, $locale);
 */
class TranslationServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    protected $app;
    protected $path;
    protected $locales;
    protected $fallback_locale;

    public function boot(Application $app)
    {
        $app['translator.init']();
    }

    public function register(Container $app)
    {
        $this->app = $app;
        $app['translator.init'] = $app->protect(function () use ($app) {
            $this->path = $app['translator.path'];
            $this->locales = $app['translator.locales'];
            $this->fallback_locale = $app['translator.fallback_locale'];
        });

        $app['translator.loader'] = function () use ($app) {
            return new FileLoader(new Filesystem(), $this->path);
        };

        $app['translator'] = function () use ($app) {
            return new Translator($app['translator.loader'], $app['locale'] ?? $this->fallback_locale);
        };
    }

    public function getConfigTreeBuilder(): NodeDefinition
    {
        $builder = new TreeBuilder();
        $node = $builder->root('translator');

        $node->children()
            ->scalarNode('path')->isRequired()->end()
            ->scalarNode('locales')->isRequired()->end()
            ->scalarNode('fallback_locale')->defaultValue('en')->end()
            ->end();

        return $node;
    }
}
