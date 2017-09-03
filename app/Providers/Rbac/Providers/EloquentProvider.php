<?php

namespace App\Providers\Rbac\Providers;

use App\Providers\Rbac\Interfaces\UserInterface;
use App\Providers\Rbac\Interfaces\ProviderInterface;
use Silex\Application;

/**
 * Class EloquentProvider
 * @package App\Providers\Rbac\Providers
 *
 * @property \App\Application $app
 */
class EloquentProvider implements ProviderInterface
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get user By ID.
     *
     * @param $id
     * @return UserInterface|null
     */
    public function retrieveById($id)
    {
        return $this->app->model('user')->find($id);
    }

    /**
     * Get user by Token.
     *
     * @param string $token
     * @return UserInterface|null
     */
    public function retrieveByToken(string $token)
    {
        return $this->app->model('user')->where('token', '=', $token)->first();
    }

    /**
     * Get User by Credentials.
     *
     * @param array $credentials
     * @return UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->app->model('user')->where($credentials)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Jenssegers\Mongodb\Eloquent\Builder
     */
    public function newInstance()
    {
        return $this->app->model('user');
    }
}