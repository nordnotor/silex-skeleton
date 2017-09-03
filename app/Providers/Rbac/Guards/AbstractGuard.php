<?php

namespace App\Providers\Rbac\Guards;

use App\Model;
use Silex\Application;

use App\Providers\Rbac\Interfaces\{
    ProviderInterface,
    UserInterface,
    GuardInterface
};

/**
 * Class security
 * @package e1\providers\RBAC
 *
 * @property \App\Application $app
 * @property UserInterface|null $user
 * @property ProviderInterface $provider
 */
abstract class AbstractGuard implements GuardInterface
{
    protected $app;
    protected $provider;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}