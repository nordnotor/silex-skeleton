<?php

namespace App\Site\Controllers;

use App\Application;
use App\Models\User;
use App\Site\ActiveCrud;
use Symfony\Component\HttpFoundation\Request;

class SiteController extends ActiveCrud
{
    public function connect(\Silex\Application $app)
    {
        $converter = $app['converter'];
        $controllers = parent::connect($app);

        $controllers->get('/site/dashboard', [$this, 'dashboard'])->bind($this->prefix('dashboard'));

        return $controllers;
    }

    public function dashboard(Application $app, Request $request)
    {
        return $this->render([], 'dashboard');
    }
}
