<?php

namespace App\Subscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class ErrorSubscriber
 * @package App\Middleware
 *
 * @property \App\Application $app
 */
class ErrorSubscriber implements EventSubscriberInterface
{
    protected $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['handle', -8]
        ];
    }

    public function handle(GetResponseForExceptionEvent $event)
    {
        /** @var GetResponseForExceptionEvent $event */

        $exception = $event->getException();
        $code = $exception->getCode();

        $templates = [
            'errors/' . $code . '.html.twig',
            'errors/' . substr($code, 0, 2) . 'x.html.twig',
            'errors/' . substr($code, 0, 1) . 'xx.html.twig',
            'errors/default.html.twig',
        ];

        return new Response($this->app['twig']->resolveTemplate($templates)->render([
            'exception' => $exception,
        ]), 200);
    }

}