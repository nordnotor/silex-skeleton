<?php

namespace App\Subscriber;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FormatterSubscriber
 * @package App\Subscriber
 *
 * @property \App\Application $app
 */
class FormatterSubscriber implements EventSubscriberInterface
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['before']],
            KernelEvents::VIEW => [['after']],
        ];
    }

    public function before(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (0 === strpos($request->headers->get('Accept'), 'application/json')) {
            $request->setRequestFormat('json');

            $data = json_decode($request->getContent(), true);
            $request->request->replace(empty($data) ? [] : $data);
        }

        if (0 === strpos($request->headers->get('Accept'), 'text/html')) {
            $request->setRequestFormat('html');
        }

        if (0 === strpos($request->headers->get('Content-Type'), 'multipart/form-data')) {
            $request->request->replace($_POST);
        }
    }

    public function after(GetResponseForControllerResultEvent $event)
    {
        $response = null;
        $request = $event->getRequest();
        $response = $event->getControllerResult();

        if (is_array($response)) {

            list($viewPath, $status, $result) = $response ?? ['', 200, []];

            if ($request->getRequestFormat() === 'json') {
                $response = $this->app->json($result, $status);
            } elseif ($request->getRequestFormat() === 'html') {
                $response = $this->app->render($viewPath, $result, new Response('', $status));
            }

        } elseif (is_string($response)) {

            if ($request->getRequestFormat() === 'json') {
                $response = $this->app->json(['data' => $response], 200);
            } elseif ($request->getRequestFormat() === 'html') {
                $response = new Response($response);
            }
        }

        if ($response instanceof Response) {
            $event->setResponse($response);
        } elseif (null !== $response) {
            $event->setControllerResult($response);
        }
    }
}