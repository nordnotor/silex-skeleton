<?php

namespace App\Site\Controllers;

use App\Application;
use App\Models\User;
use App\Site\ActiveCrud;
use Symfony\Component\HttpFoundation\Request;

class UserController extends ActiveCrud
{
    public function connect(\Silex\Application $app)
    {
        $converter = $app['converter'];
        $controllers = parent::connect($app);

        $convert_values = function ($value, Request $request) {
            if ($request->request->has('birthday')) {
                $data_expire = $request->request->get('birthday');
                $request->request->set('birthday', empty($data_expire) ? null : strtotime($data_expire));
            }
            if ($request->request->has('phone')) {
                $request->request->set('phone', (int)$request->get('phone'));
            }
        };

        $controllers->get('/get/all', [$this, 'all'])->bind($this->prefix('all'));

        $controllers->post('/', [$this, 'upsert'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName, 'required' => false]))
            ->convert('convert_values', $convert_values)->bind($this->prefix('create'));

        $controllers->put('/{id}', [$this, 'upsert'])->assert('id', '[^/]+')
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->convert('convert_values', $convert_values)->bind($this->prefix('update'));

        $controllers->put('/update/ajax/{id}', [$this, 'updateAjax'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->convert('convert_values', $convert_values)->bind($this->prefix('update.ajax'));

        $controllers->post('/create/ajax/', [$this, 'createAjax'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName, 'required' => false]))
            ->convert('convert_values', $convert_values)->bind($this->prefix('create.ajax'));

        return $controllers;
    }

    public function all(Application $app, Request $request)
    {
        $term = $request->query->get('term');
        $query = $app->model($this->modelName);

        if (!empty($term)) {

            $query = $query->where(function ($query) use ($term) {

                $query->where('first_name', 'like', "%$term%")
                    ->orWhere('middle_name', 'like', "%$term%")
                    ->orWhere('last_name', 'like', "%$term%");
            });
        }

        return $this->render($query->get()->toArray());
    }
}
