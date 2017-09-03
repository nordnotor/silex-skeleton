<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * #FIXES
 * see shouldUseCollections() [/jenssegers/mongodb/src/Jenssegers/Mongodb/Query/Builder]
 * f-style code (after Eloquent 5.3 Query Builder Returns a Collection)
 */
function app()
{
    return new class()
    {
        public function version()
        {
            return '5.4';
        }
    };
}


$app = new App\Application([
    # debug
    'debug' => APP_DEBUG,
    # app dirs
    'root.dir' => dirname(__DIR__),
    'storage.dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
    'resources.dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources',
    'cache.dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . '/runtime/cache',
]);

$app->register(new App\Providers\Config\ConfigurationServiceProvider('yml', APP_DEBUG ? 'dev' : 'prod', $app['cache.dir']));


$app->register(new \App\Providers\FileSystem\FlySystemServiceProvider(), ['flysystem.filesystems' => [
    'local' => [
        'adapter' => 'League\Flysystem\Adapter\Local',
        'args' => [__DIR__ . '/tmp'],
        'config' => [
            'asset.path' => '/tmp/'
        ],
    ]
]]);
$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new \App\Providers\illuminateTranslation\TranslationServiceProvider());
$app->register(new \App\Providers\Eloquent\EloquentMongoDbServiceProvider());
$app->register(new \App\Providers\Twig\TwigServiceProvider());
$app->register(new Silex\Provider\AssetServiceProvider());
$app->register(new \App\Providers\Predis\PredisServiceProvider());
$app->register(new \App\Providers\Convert\ConverterServiceProvider());
$app->register(new \App\Providers\illuminateValidation\ValidationServiceProvider());
$app->register(new \App\Providers\illuminateTranslation\TranslationServiceProvider());
$app->register(new \App\Providers\Rbac\SecurityServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider());

$app->mount('/', new \App\Site\Controllers\AuthController('layout/auth', 'auth', 'widget/auth'));
$app->mount('/site/user', new \App\Site\Controllers\UserController('layout/main', 'user', 'widget/user'));
$app->mount('/site/user', new \App\Site\Controllers\SiteController('layout/main', 'site', 'widget/site'));

# Service
$app->register(new \App\Services\AttachmentService('attachment'));
$app->register(new \App\Services\CommentService('comment'));

# Extend
$app->extend('twig', function ($twig, $app) {
    $twig->addGlobal('user', $app['security.user']);
    $twig->addGlobal('breadcrumbs', ['name' => '', 'min' => '']);
    return $twig;
});


$app['dispatcher']->addSubscriber(new \App\Subscriber\ControllerSubscriber($app));
$app['dispatcher']->addSubscriber(new \App\Subscriber\FormatterSubscriber($app));
$app['dispatcher']->addSubscriber(new \App\Subscriber\ErrorSubscriber($app));

Request::enableHttpMethodParameterOverride();

return $app;
