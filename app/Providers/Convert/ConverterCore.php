<?php

namespace App\Providers\Convert;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConverterCore
 * @package App\Providers\Convert
 *
 * @property Application $app
 */
abstract class ConverterCore
{
    protected $app;

    public function __construct(Application $app, array $params = [])
    {
        $this->app = $app;
        foreach ($params as $name => $param) {
            $this->$name = $param;
        }
    }

    abstract public static function converterName(): string;

    abstract public function convert($attribute = null, Request $request);
}