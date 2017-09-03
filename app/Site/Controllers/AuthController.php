<?php

namespace App\Site\Controllers;

use App\Application;
use App\Models\Login;
use App\Models\User;
use App\Providers\Rbac\Tokens\SessionToken;
use App\Site\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Site\Middleware\AuthSecurityMiddleware;

class AuthController extends Controller
{
    public static $afterLogin = 'site.dashboard';
    public static $afterLogout = 'auth.login';

    public function getMiddlewares(): array
    {
        return [];
    }

    public function connect(\Silex\Application $app)
    {
        $controllers = parent::connect($app);

        # register
        $controllers->post('/register', [$this, 'registerProcess']);
        $controllers->get('/register', [$this, 'beforeForm'])->convert('view', function () {
            return 'register';
        })->bind('auth.register');

        # login
        $controllers->post('/', [$this, 'loginProcess']);
        $controllers->get('/', [$this, 'beforeForm'])->convert('view', function () {
            return 'login';
        })->bind('auth.login');

        # logout
        $controllers->get('/logout', [$this, 'logout'])->bind('auth.logout');

        return $controllers;
    }

    # before login and register render view

    public function beforeForm(string $view, Application $app, Request $request)
    {
        $user = $this->app['security.user'];

        if (isset($user, $user->_id)) {
            $url = $request->headers->get('Referer', $app->url(self::$afterLogin));
            return $app->redirect($url);
        }
        return $this->render([], $view);
    }

    # login
    public function loginProcess(Application $app, Request $request)
    {
        /** @var Login $model */

        $model = $app->model('login');
        $form = $request->request->get('form', []);

        if ($model->validate($form) && $model->login()) {

            $token = new SessionToken([
                'secret_key' => 't3o@1k$e%n5',
                'permanent' => $model->remember
            ]);

            $token->setUser($model->getUserAttribute());

            $this->app['security.storage']->setToken($token);

            return $app->redirect($app->url(self::$afterLogin));
        }
        return $this->render($model->toArray(), 'login');
    }

    # register
    public function registerProcess(Application $app, Request $request)
    {
        /** @var User $model */
        $model = $app->model('user');
        $model->setScenario(User::SCENARIO_REGISTRATION);

        $data = $request->request->get('form', []);

        if ($model->validate($data) && $model->save()) {
            return $app->redirect($app->url('auth.login'));
        }
        return $this->render($model->toArray(true), 'register');
    }

    # logout
    public function logout(Application $app, Request $request)
    {
        $this->app['security.storage']->getToken()->setUser(null);

        return $app->redirect($app->url($this::$afterLogout));
    }
}
