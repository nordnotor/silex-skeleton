<?php

namespace App\Providers\Convert\Converter;

use Silex\Application;
use App\Providers\Convert\ConverterCore;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Model
 * @package App\Providers\Convert\Converter
 *
 * @property Application $app
 * @property string $modelName
 * @property string $idName
 * @property bool $required
 */
class Model extends ConverterCore
{
    protected $modelName;
    protected $idName = 'id';
    protected $required = true;

    public function convert($attribute = null, Request $request)
    {
        $id = $request->get($this->idName);
        $model = $this->app->model($this->modelName);

        if ($id && $modelById = $model->find($id)) {
            return $modelById;
        } elseif ($this->required) {
            return $this->app->abort(404, 'The requested record does not exist.');
        }
        return $model;
    }

    public static function converterName(): string
    {
        return 'model';
    }
}