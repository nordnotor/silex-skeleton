<?php

namespace App\Subscriber;

use App\Interfaces\MiddlewareInterface;
use App\Components\Crud\CoreController;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Interfaces\ControllerMiddlewareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class ControllerSubscriber
 * @package App\Subscriber
 *
 * @property \App\Application $app
 */
class ControllerSubscriber implements EventSubscriberInterface
{
    protected $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['middlewares'], ['locale']],
        ];
    }

    public function middlewares(Event $event)
    {
        /** @var CoreController $controller @var string $method */
        if (is_callable($this->app->request()->attributes->get('_controller'))) {
            return;
        }

        list($controller, $method) = $this->app->request()->attributes->get('_controller');

        if ($controller instanceof ControllerMiddlewareInterface) {

            foreach ($controller->getMiddlewares() as $class) {

                $middleware = new $class();

                if (!$middleware instanceof MiddlewareInterface) {
                    $this->app->abort("Middleware {$class} must be instance of 'MiddlewareInterface'.");
                }

                if ($middleware->support($event)) {
                    $middleware->handle($event, $this->app);
                }
            }
        }
    }

    public function locale(Event $event)
    {
        if (is_callable($this->app->request()->attributes->get('_controller'))) {
            return;
        }

        /** @var CoreController $controller @var string $method */
        list($controller, $method) = $this->app->request()->attributes->get('_controller');

        $controller->getControllersFactory()->assert('_locale', $this->app['translator.locales']);
    }
}