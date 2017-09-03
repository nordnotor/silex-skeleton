<?php

namespace App\Site;

use App\Model;
use Silex\Application;
use App\Interfaces\ModelInterface;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Base CRUD controller.
 *
 * Class ActiveCrud
 * @package App\Site
 */
class ActiveCrud extends Controller
{
    /**
     * For add appends and relations in need default action
     * @var array
     */
    public $additions = [

        # appends
        'append.view' => [],
        'append.upsert' => [],
        'append.list' => [],

        # relations
        'relation.view' => [],
        'relation.upsert' => [],
        'relation.list' => [],
    ];

    public function connect(Application $app)
    {
        $converter = $app['converter'];
        $controllers = parent::connect($app);

        $controllers->get('/list', [$this, 'index'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName, 'required' => false]))
            ->bind($this->prefix('index'));

        $controllers->get('/{id}', [$this, 'view'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->assert('id', '[^/]+')
            ->bind($this->prefix('view'));

        $controllers->patch('/{id}', [$this, 'restore'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->assert('id', '[^/]+')
            ->bind($this->prefix('restore'));

        $controllers->delete('/{id}', [$this, 'delete'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->assert('id', '[^/]+')
            ->bind($this->prefix('delete'));

        $controllers->post('/', [$this, 'upsert'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName, 'required' => false]))
            ->bind($this->prefix('create'));

        $controllers->put('/{id}', [$this, 'upsert'])
            ->convert('model', $converter->get('model', ['modelName' => $this->modelName]))
            ->bind($this->prefix('update'));

        return $controllers;
    }

    /**
     * @param ModelInterface|Model|Builder $model
     * @param Application $app
     * @param Request $request
     * @return array
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \InvalidArgumentException
     */
    public function index(ModelInterface $model, Application $app, Request $request)
    {
        $data = $request->query->all();
        $limit = (int)$request->query->get('per_page', 10);
        $current_page = (int)$request->query->get('current_page', 1);

        # search
        $classNameSearch = '\App\Models\Search\\' . ucfirst($this->modelName) . 'Search';
        if (class_exists($classNameSearch)) {

            $modelSearch = new $classNameSearch;
            $modelSearch->setScenario($classNameSearch::SCENARIO_LIST);

            if ($modelSearch->validate($data)) {
                $model = $modelSearch->search();
            } else {

                return $this->render([
                    'pager' => $modelSearch->toArray(true)
                ], 'index');
            }
        }

        if (isset($this->additions['relation.list'])) {
            $model->with($this->getAdditions('relation.list'));
        }

        /** @var LengthAwarePaginator $pager */
        $pager = $model->paginate($limit, ['*'], 'current_page', $current_page);
        $pager->setPath($app->url($this->prefix('index')));

        foreach ($data as $key => $value) {
            $pager->appends($key, $value);
        }

        # add append to models
        foreach ($pager->items() as $item) {
            /** @var Model|Builder $item # add appends */
            $item->append($this->getAdditions('append.list'));
        }

        return $this->render([
            'pager' => $pager->toArray()
        ], 'index');
    }

    /**
     * @param ModelInterface|Model|Builder $model
     * @param Application $app
     * @param Request $request
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function upsert(ModelInterface $model, Application $app, Request $request)
    {
        /** @var array $data */
        $data = $request->request->all();

        $model->setScenario($data['_scenario'] ?? '');

        unset($data['_scenario'], $data['_method']);

        # add appends
        $model->append($this->getAdditions('append.upsert'));
        # add relations
        $model->load($this->getAdditions('relation.upsert'));

        if (!empty($data) && $model->validate($data)) {

            if ($model->save()) {
                list($type, $message) = ['success', 'Record successfully saved.'];
            } else {
                list($type, $message) = ['danger', 'Record not saved, something went wrong.'];
            }

            $this->setFlesh($type, $message);

            return $app->redirect($app->url($this->modelName . '.view', [
                'id' => $model->getKey()
            ]));
        }

        return $this->render([
            'model' => $model->toArray(true),
        ], $model->getKey() ? 'update' : 'create', 422);
    }

    /**
     * @param ModelInterface|Model|Builder $model
     * @param Application $app
     * @param Request $request
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function view(ModelInterface $model, Application $app, Request $request)
    {
        $model->setScenario('view');

        # add appends
        $model->append($this->getAdditions('append.view'));
        # add relations
        $model->load($this->getAdditions('relation.view'));

        return $this->render([
            'model' => $model->toArray(true),
        ], 'view');
    }

    /**
     * @param ModelInterface|Model|Builder $model
     * @param Application $app
     * @param Request $request
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function delete(ModelInterface $model, Application $app, Request $request)
    {
        if ($model->setScenario('delete')->delete()) {
            return $app->redirect($request->headers->get('Referer', $app->url($this->modelName . '.index')));
        }
        return $app->abort(500);
    }

    /**
     * @param ModelInterface|Model|Builder $model
     * @param Application $app
     * @param Request $request
     * @return mixed
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function restore(ModelInterface $model, Application $app, Request $request)
    {
        if ($model->setScenario('restore')->restore()) {
            return $app->redirect($app->url($this->modelName . '.index'));
        }
        return $app->abort(500);
    }

    public function setFlesh($type, $message)
    {
        if ($this->app['session']) {
            $this->app['session']->getFlashBag()->add($type, $message);
        }
    }
}
