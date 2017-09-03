<?php

namespace App\Components\Crud;

use App\Interfaces\ControllerInterface;
use App\Providers\Rbac\AbstractGuard;
use App\Providers\Rbac\Interfaces\GuardInterface;
use Silex\Application;
use App\Interfaces\MiddlewareInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class BaseController
 * @package App\Components\Crud
 *
 * @property \App\Application $app
 * @property array $secure
 *
 * @property string $prefix
 * @property string $modelName
 * @property string $layoutTemplate
 * @property string|null $pathTemplate
 *
 * @property ControllerCollection $controllers_factory
 * @property string $checker
 */
abstract class CoreController implements ControllerInterface
{
    public $app;
    public $modelName;
    public $additions = [];

    protected $prefix;
    protected $secure;
    protected $security;
    protected $controllers_factory;

    public function __construct(string $security, string $modelName = '', string $prefix = '')
    {
        $this->modelName = empty($modelName) ? substr(strrchr(get_called_class(), "\\"), 1) : $modelName;
        $this->prefix = "$this->modelName$prefix";
        $this->security = $security;
    }

    public function connect(Application $app)
    {
        $this->app = $app;
        $this->setControllersFactory($app['controllers_factory']);

        return $this->getControllersFactory();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getAdditions(string $name): array
    {
        return $this->additions[$name] ?? [];
    }

    /**
     * @param string $string
     * @return string
     */
    public function prefix(string $string): string
    {
        return "$this->prefix.$string";
    }

    /**
     * @return ControllerCollection
     */
    public function getControllersFactory(): ControllerCollection
    {
        return $this->controllers_factory;
    }

    /**
     * @param mixed $controllers_factory
     */
    public function setControllersFactory(ControllerCollection $controllers_factory)
    {
        $this->controllers_factory = $controllers_factory;
    }


    /**
     * Renders a template.
     *
     * @param array $data An array of parameters to pass to the template
     * @param string $template The template name
     * @param int $status
     * @return array
     */
    public function render(array $data = [], string $template = null, $status = 200): array
    {
        $format = $this->app->request()->getRequestFormat();

        return $this->app->renderResult([
            'success' => in_array($status, [200, 201]),
            'layout' => "$this->layoutTemplate.$format.twig",
            'modelName' => $this->modelName,
            'data' => $data
        ], "$this->pathTemplate/$template.$format.twig", $status);
    }
}