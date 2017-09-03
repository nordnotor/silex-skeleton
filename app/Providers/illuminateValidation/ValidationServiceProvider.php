<?php

namespace App\Providers\illuminateValidation;

use Pimple\ServiceProviderInterface;
use Silex\Application;
use Illuminate\Validation\Factory;
use Illuminate\Container\Container;
use Illuminate\Validation\DatabasePresenceVerifier;

/**
 * Class ValidationServiceProvider
 * @package e1\providers\illuminateValidation
 *
 * @REQUIRE:
 *
 * @see: https://github.com/illuminate/validation
 * composer require illuminate/validation
 *
 * @REGISTER:
 *
 * $app->register(new \App\Providers\illuminateValidation\ValidationServiceProvider());
 *
 * @USAGE:
 * $errors = $app['']->make($data, $rules, $customMessages);
 *
 * if ($error->fails()) {
 *      # load error message to error class
 *      $validateErrors = $error->errors();
 * }
 *
 */
class ValidationServiceProvider implements ServiceProviderInterface
{
    public function register(\Pimple\Container $app)
    {
        $app['validation.presence'] = function () use ($app) {
            return new DatabasePresenceVerifier($app['eloquent.capsule']->getDatabaseManager());
        };

        $app['validator'] = function () use ($app) {

            $validator = new Factory($app['translator'], new Container());
            $validator->setPresenceVerifier($app['validation.presence']);

            return $validator;
        };
    }
}

