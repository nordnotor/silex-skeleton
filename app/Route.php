<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Providers\Rbac\Interfaces\GuardInterface;

/**
 * Class Route
 * @package e1
 */
class Route extends \Silex\Route
{
//    public function secure($roles, $redirect_url = null, GuardInterface $guard)
//    {
//        $this->before(function (Request $request, Application $app) use ($roles, $redirect_url, $guard) {
//
//            if ($guard->isGranted($roles, $request)) {
//                return;
//            }
//            return empty($redirect_url) ? $app->abort(403) : $app->redirect($app->url($redirect_url));
//        });
//
//        $this->after(function (Request $request, Response $response) use ($roles, $redirect_url, $guard) {
//            $guard->store($request, $response);
//        });
//
//        return $this;
//    }
}
