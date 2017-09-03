<?php

namespace App\Providers\Twig;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\Twig\RuntimeLoader;
//use Symfony\Bridge\Twig\AppVariable;
use App\Providers\Twig\Extensions\AppVariable; # own SecurityExtension
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\DumpExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
//use Symfony\Bridge\Twig\Extension\SecurityExtension;
use App\Providers\Twig\Extensions\SecurityExtension; # own SecurityExtension
//use Symfony\Bridge\Twig\Extension\TranslationExtension;
use App\Providers\Twig\Extensions\TranslationExtension; # own TranslationExtension from laravel
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Extension\HttpKernelRuntime;

/**
 * Class TwigServiceProvider
 * @package e1\providers\Twig
 *
 * @REQUIRE
 *
 * @see https://github.com/symfony/twig-bridge
 * composer require symfony/twig-bridge
 *
 * @see http://twig.sensiolabs.org/
 * composer require twig/twig
 *
 * @REGISTER:
 *
 * $app->register(new \App\Providers\Twig\TwigServiceProvider(),[
 *      'twig.path' => __DIR__.'/views',
 *      'twig.options' => [
 *           'cache' => __DIR__ . '/runtime/cache/twig'
 *      ],
 *     'twig.form.templates' => [
 *           'bootstrap_3_layout.html.twig'
 *      ],
 * ]);
 *
 * @USAGE:
 *
 * @see http://twig.sensiolabs.org;
 * @see http://silex.sensiolabs.org/doc/2.0/providers/twig.html;
 *
 * $app['twig']->render($pathToTemplate, $data);
 */
class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['twig.options'] = array();
        $app['twig.form.templates'] = array('form_div_layout.html.twig');
        $app['twig.path'] = array();
        $app['twig.templates'] = array();

        $app['twig'] = function ($app) {
            $app['twig.options'] = array_replace(
                array(
                    'charset' => $app['charset'],
                    'debug' => $app['debug'],
                    'strict_variables' => $app['debug'],
                ), $app['twig.options']
            );

            $twig = $app['twig.environment_factory']($app);
            // registered for BC, but should not be used anymore
            // deprecated and should probably be removed in Silex 3.0
            $twig->addGlobal('app', $app);

            if ($app['debug']) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }

            if (class_exists('Symfony\Bridge\Twig\Extension\RoutingExtension')) {
//                $app['twig.app_variable'] = function () use ($app) {
                $var = new AppVariable();
                if (isset($app['request_stack'])) {
                    $var->setRequestStack($app['request_stack']);
                }
                $var->setDebug($app['debug']);

                $app['twig.app_variable'] = $var;
//                };

                $twig->addGlobal('global', $app['twig.app_variable']);

                if (isset($app['request_stack'])) {
                    $twig->addExtension(new HttpFoundationExtension($app['request_stack']));
                    $twig->addExtension(new RoutingExtension($app['url_generator']));
                }

                if (isset($app['translator'])) {
                    $twig->addExtension(new TranslationExtension($app['translator']));
                }


                $twig->addExtension(new SecurityExtension('security.authorization_checker', $app));


                if (isset($app['fragment.handler'])) {
                    $app['fragment.renderer.hinclude']->setTemplating($twig);

                    $twig->addExtension(new HttpKernelExtension($app['fragment.handler']));
                }

                if (isset($app['assets.packages'])) {
                    $twig->addExtension(new AssetExtension($app['assets.packages']));
                }

                if (isset($app['form.factory'])) {
                    $app['twig.form.engine'] = function ($app) use ($twig) {
                        return new TwigRendererEngine($app['twig.form.templates'], $twig);
                    };

                    $app['twig.form.renderer'] = function ($app) {
                        $csrfTokenManager = isset($app['csrf.token_manager']) ? $app['csrf.token_manager'] : null;

                        return new TwigRenderer($app['twig.form.engine'], $csrfTokenManager);
                    };

                    $twig->addExtension(new FormExtension($app['twig.form.renderer']));

                    // add loader for Symfony built-in form templates
                    $reflected = new \ReflectionClass('Symfony\Bridge\Twig\Extension\FormExtension');
                    $path = dirname($reflected->getFileName()) . '/../Resources/views/Form';
                    $app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($path));
                }

                if (isset($app['var_dumper.cloner'])) {
                    $twig->addExtension(new DumpExtension($app['var_dumper.cloner']));
                }

                if (class_exists(HttpKernelRuntime::class)) {
                    $twig->addRuntimeLoader($app['twig.runtime_loader']);
                }
            }

            return $twig;
        };

        $app['twig.loader.filesystem'] = function ($app) {
            return new \Twig_Loader_Filesystem($app['twig.path']);
        };

        $app['twig.loader.array'] = function ($app) {
            return new \Twig_Loader_Array($app['twig.templates']);
        };

        $app['twig.loader'] = function ($app) {
            return new \Twig_Loader_Chain(array(
                $app['twig.loader.array'],
                $app['twig.loader.filesystem'],
            ));
        };

        $app['twig.environment_factory'] = $app->protect(function ($app) {
            return new \Twig_Environment($app['twig.loader'], $app['twig.options']);
        });

        $app['twig.runtime.httpkernel'] = function ($app) {
            return new HttpKernelRuntime($app['fragment.handler']);
        };

        $app['twig.runtimes'] = function ($app) {
            return array(
                HttpKernelRuntime::class => 'twig.runtime.httpkernel',
                TwigRenderer::class => 'twig.form.renderer',
            );
        };

        $app['twig.runtime_loader'] = function ($app) {
            return new RuntimeLoader($app, $app['twig.runtimes']);
        };
    }
}
