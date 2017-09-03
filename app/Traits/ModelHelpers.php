<?php

namespace App\Traits;

use App\Application;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
 * Class modelSoft
 * @package e1\Traits
 */
trait ModelHelpers
{
    /**
     * Get app[$name] or app if $name == null
     *
     * @param string|null $name
     * @return mixed|Application|\Silex\Application
     */
    public function app($name = null)
    {
        $app = self::globalApp();

        if ($name) {
            return $app[$name] ?? null;
        }
        return $app;
    }

    /**
     * @return \e1\Application
     * @throws \Error
     */
    public static function globalApp()
    {
        $callable = Eloquent::getGlobalScope('app');
        if (is_callable($callable) && $app = $callable()) {
            return $app;
        }
        throw new \Error('\'app\' in Global Scope is not callable or ont exist.', 500);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     * @param  mixed|null $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return parent::getAttribute($key) ?? $default;
    }

    /**
     * @param \string[] ...$fields
     */
    public function forget(string ...$fields)
    {
        foreach ($fields as $field) {
            array_forget($this->attributes, $field);
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    public function setRawAttribute(string $field, $value)
    {
        $this->attributes[$field] = $value;
    }


    /**
     * Get Origin Model.
     *
     * @return static
     */
    public function getOriginModel()
    {
        return new static($this->getOriginal());
    }

    /**
     * Get base name class.
     *
     * @return string
     */
    public function basename()
    {
        return class_basename($this);
    }
}

