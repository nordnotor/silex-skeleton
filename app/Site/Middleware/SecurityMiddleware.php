<?php

namespace App\Site\Middleware;

use App\Application;
use App\Interfaces\MiddlewareInterface;
use App\Site\Controllers\AuthController;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SecurityMiddleware implements MiddlewareInterface
{
    /**
     * @param Event|GetResponseEvent $event
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function handle(Event $event, Application $app)
    {
        # todo: methods getRoles() | getListeners()
        list($listeners, $roles, $security) = $app['security.map']->getListeners($app->request());

        if (null !== $user = $app['security.user']) {

            if (!empty($roles) && !$user->isRole($roles)) {
                $app->abort(403);
            }
            return;
        }
        $event->setResponse($app->redirect($app->url(AuthController::$afterLogout)));
    }

    public function support(Event $event): bool
    {
        return $event instanceof GetResponseEvent;
    }
}