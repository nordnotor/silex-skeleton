<?php

namespace App\Services;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class CoreService
 * @package App\Services
 *
 * @property \App\Application $app
 * @property string $serviceName
 */
class CoreService implements ServiceProviderInterface
{
    protected $app;
    protected $serviceName;

    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $this->app = $app;
        $app["service.$this->serviceName"] = $this;
    }

    /**
     * coreService constructor.
     * @param null|string $serviceName
     */
    public function __construct($serviceName = null)
    {
        $this->serviceName = $serviceName ?? substr(strrchr(get_called_class(), "\\"), 1);
    }
}