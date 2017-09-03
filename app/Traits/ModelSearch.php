<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property \Illuminate\Database\Eloquent\Builder $query
 */
trait ModelSearch
{
    protected $query;

    /**
     * @param string|null $tableName
     * @return Model|modelSearch
     */
    public function queryFilter($tableName = null): Model
    {
        $name = str_replace('\\', '', Str::snake(Str::plural(class_basename(parent::class))));

        $this->query = $this->setTable($tableName ?? $name)->newQuery();

        return $this;
    }

    /**
     * @param string $name
     * @param string $function
     * @return \Illuminate\Database\Eloquent\Builder|Model|modelSearch
     */
    public function addFilter(string $name, string $function)
    {
        $attribute = $this->getAttribute($name);

        if (!empty($attribute)) {

            $params = func_get_args();
            unset($params[1]);

            $this->query = call_user_func_array([$this->query, $function], $params);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $function
     * @param array ...$params
     * @return \Illuminate\Database\Eloquent\Builder|Model|modelSearch
     */
    public function addCustomFilter(string $name, string $function, ...$params)
    {
        $attribute = $this->getAttribute($name);

        if (!empty($attribute)) {
            $this->query = call_user_func_array([$this->query, $function], $params);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $function
     * @return $this
     */
    public function addBoolFilter(string $name, string $function)
    {
        $attribute = $this->getAttribute($name);

        if (!empty($attribute) || $attribute === 0) {

            $params = func_get_args();
            unset($params[1]);

            $this->query = call_user_func_array([$this->query, $function], $params);
        }
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQueryFilter()
    {
        return $this->query;
    }
}

